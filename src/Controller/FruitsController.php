<?php

namespace App\Controller;

use App\Entity\Fruits;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class FruitsController extends AbstractController
{

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/fruits', name: 'app_fruits')]
    public function getFruits(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, int $page): JsonResponse
    {
        $itemPerPage = 10;

        try {
            $routeParams = $request->attributes->get('_route_params');
            $nameSearch = $request->query->get('name');
            $familySearch = $request->query->get('family');
            $nameFilter = $request->query->all('nfilter');
            $familyFilter = $request->query->all('ffilter');
            $onlyFavs = $request->query->get('favs');
            $page = $routeParams['page'];
            $skip = ($page - 1) * $itemPerPage;
            $whereCond = $onlyFavs ? 'f.fav = TRUE' : '1 = 1';

            $queryBuilder = $entityManager->getRepository(Fruits::class)->createQueryBuilder('f')->where($whereCond);
            if (isset($nameSearch) && $nameSearch != '') {
                $queryBuilder->andWhere('f.name LIKE :name')->setParameter("name", '%' . $nameSearch . '%');
            }
            if (isset($familySearch) && $familySearch != '') {
                $queryBuilder->andWhere('f.family LIKE :family')->setParameter("family", '%' . $familySearch . '%');
            }
            if (is_array($nameFilter)) {
                $stm = $queryBuilder->expr()->orX();
                foreach ($nameFilter as $filter) {
                    $stm->add($queryBuilder->expr()->eq('f.name', "'" . $filter . "'"));
                }
                $queryBuilder->andWhere($stm);
            }
            if (is_array($familyFilter)) {
                $stm = $queryBuilder->expr()->orX();
                foreach ($familyFilter as $i => $filter) {
                    $stm->add($queryBuilder->expr()->eq('f.family', "'" . $filter . "'"));
                }
                $queryBuilder->andWhere($stm);
            }

            $fruits = $queryBuilder->setFirstResult($skip)->setMaxResults($itemPerPage)->getQuery()->getResult();
            $count = $queryBuilder->resetDQLPart('orderBy')->setFirstResult(0)
                ->select('COUNT(f)')
                ->getQuery()
                ->getSingleScalarResult();
            $fruitsJson = $serializer->serialize([
                "skip" => $skip,
                "page" => $page,
                "fruits" => $fruits,
                "total" => $count,
                "itemPerPage" => $itemPerPage
            ], 'json');
            return new JsonResponse($fruitsJson, 200, [], true);
        } catch (\Exception $e) {
            return new JsonResponse([
                "message" => "There was an error fetching data"
            ], 500);
        }
    }
    public function filters(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        try {
            $nameQuery = $entityManager->getRepository(Fruits::class)->createQueryBuilder('f')
                ->select('f.name')
                ->distinct()
                ->getQuery()
                ->getResult();

            $familiesQuery = $entityManager->getRepository(Fruits::class)->createQueryBuilder('f')
                ->select('f.family')
                ->distinct()
                ->getQuery()
                ->getResult();

            $filters = $serializer->serialize([
                "names" => $this->map($nameQuery, 'name'),
                "families" => $this->map($familiesQuery, 'family'),
            ], 'json');


            return new JsonResponse($filters, 200, [], true);
        } catch (\Exception $e) {
            return new JsonResponse([
                "message" => "There was an error fetching data"
            ], 500);
        }

    }

    protected function map($arr, $label)
    {
        return array_map(function ($val) use ($label) {
            return $val[$label];
        }, $arr);
    }

    public function getFavs(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $queryBuilder = $entityManager->getRepository(Fruits::class)->createQueryBuilder('f')->where('f.fav = TRUE');

        $favouringFruits = $queryBuilder->getQuery()->getResult();
        $response = $serializer->serialize([
            "fruits" => $favouringFruits
        ], 'json');
        return new JsonResponse($response, 200, [], true);
    }
    public function setFav(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, int $id): JsonResponse
    {
        try {
            if (!isset($id) || !is_int($id)) {
                throw $this->createNotFoundException('id is missing');
            }
            $count = $entityManager->getRepository(Fruits::class)->createQueryBuilder('f')->select('count(f.id)')->where('f.fav = TRUE')->getQuery()->getSingleScalarResult();
            if ($count > 10) {
                return new JsonResponse([
                    "message" => "There are already 10 favouring in your list"
                ], 400);
            }

            $fruit = $entityManager->getRepository(Fruits::class)->find($id);
            if (!$fruit) {
                $this->createNotFoundException("No fruit found with this ID.");
            }
            if ($fruit->isFav()) {
                throw $this->createNotFoundException('Already is fav');
            }
            $fruit->setFav(true);

            $entityManager->persist($fruit);
            $entityManager->flush();
            return new JsonResponse([
                "message" => "success"
            ], 200);

        } catch (Exception $e) {
            return new JsonResponse([
                "message" => "Error setting favorite"
            ], 400);
        }
    }

    public function deleteFav(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, int $id): JsonResponse
    {
        try {
            if (!isset($id) || !is_int($id)) {
                throw $this->createNotFoundException('id is missing');
            }

            $fruit = $entityManager->getRepository(Fruits::class)->find($id);
            if (!$fruit) {
                $this->createNotFoundException("No fruit found with this ID.");
            }

            $fruit->setFav(false);

            $entityManager->persist($fruit);
            $entityManager->flush();
            return new JsonResponse([
                "message" => "success"
            ], 200);

        } catch (Exception $e) {
            return new JsonResponse([
                "message" => $e->getMessage()
            ], 400);
        }
    }
}