import "./bootstrap";
import { createApp } from "vue";

import App from "./components/app.vue";

const app = createApp({
    components: {
        App,
    },
});

app.mount("#app");
