import { createApp } from "vue";
import App from "./vue/App";
import router from "./vue/router";
import ElementPlus from "element-plus";
import "element-plus/dist/index.css";
import "./styles/app.css";

const app = createApp(App);
app.use(router);
app.use(ElementPlus);
app.mount("#app");
