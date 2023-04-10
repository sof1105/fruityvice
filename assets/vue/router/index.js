import {createRouter, createWebHistory} from "vue-router";
import Fruits from "../controllers/Fruits";
import Favs from "../controllers/Favs";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path:'/',
      name: 'Fruits',
      component: Fruits
    },
    {
      path:'/favs',
      name: 'favs',
      component: Favs
    },
  ]
});

export default router;
