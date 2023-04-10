<?php
namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Fruits;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:fruits:fetch')]
class FetchCommand extends Command
{
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function sendEmail($total)
    {
        $email = (new Email())
            ->from('server@example.com')
            ->to('admin@example.com')
            ->subject('Fetch is done')
            ->text("Fetch is done $total lines are saved to DB.");
        $this->mailer->send($email);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
          $output->writeln('<fg=green>Calling API</>');

            $response = $this->client->request(
                'GET',
                'https://fruityvice.com/api/fruit/all'
            );

            if ($response->getStatusCode() == 200) {
                $output->writeln('<fg=green>API call was successful. parsing data</>');
                $fruitsJson = $response->toArray();

        $total = count($fruitsJson);
        $i = 0;
        foreach ($fruitsJson as $fruit) {
            $i++;
            $output->writeln("<fg=green>Inserting $i of $total</>");

            $existing = $this->entityManager->getRepository(Fruits::class)->findOneBy(['origid' => $fruit['id']]);
            if ($existing) {
                continue;
            }

            $fruitModel = new Fruits();
            $fruitModel->setOrigid($fruit['id']);
            $fruitModel->setName($fruit['name']);
            $fruitModel->setFamily($fruit['family']);
            $fruitModel->setGenus($fruit['genus']);
            $fruitModel->setOrigorder($fruit['order']);
            $fruitModel->setCalories($fruit['nutritions']['calories']);
            $fruitModel->setCarbohydrates($fruit['nutritions']['carbohydrates']);
            $fruitModel->setProtein($fruit['nutritions']['protein']);
            $fruitModel->setSugar($fruit['nutritions']['sugar']);
            $fruitModel->setFat($fruit['nutritions']['fat']);
            $this->entityManager->persist($fruitModel);
            $this->entityManager->flush();
        }
        $output->writeln("<fg=green>Sending info mail to admin</>");

        $this->sendEmail( $total);
        $output->writeln("<fg=green>Done</>");

        return Command::SUCCESS;

            }
        $output->writeln("<fg=green>Request failed with " . $response->getStatusCode() . " status code.</>");
        return Command::FAILURE;
    }
}