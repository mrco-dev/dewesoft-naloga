<template>
    <header>
        <div class="container header_wrapper">
            <a href="/">
                <img
                    src="../../../public/images/logo.png"
                    alt="Logo"
                    class="logo"
                />
            </a>

            <button @click="login" v-if="!isAuthenticated">Prijava</button>
            <button @click="logout" v-if="isAuthenticated">Odjava</button>
        </div>
    </header>
    <div class="container">
        <div class="login_message" v-if="!isAuthenticated">
            Za prikaz Koledarja se je potrebno prijaviti.
        </div>
        <div v-if="loading">
            <span class="loader"></span>
        </div>
        <div v-else>
            <div class="event_title_wrapper" v-if="isAuthenticated">
                <h1>Dogodki</h1>
                <button @click="fetchEvents">Osveži dogodke</button>
            </div>

            <div class="event_list" v-if="events.length && isAuthenticated">
                <div
                    class="event_item"
                    v-for="event in events"
                    :key="event.id"
                    @click="toggleDescription(event)"
                >
                    <div>
                        <span class="event_item_date">{{
                            formatDate(event.start_time)
                        }}</span>
                        <span class="event_item_title">{{
                            event.summary
                        }}</span>
                    </div>
                </div>
            </div>

            <Modal :show="showModal" @close="showModal = false">
                <div v-if="selectedEvent">
                    <h2>{{ selectedEvent.summary }}</h2>
                    <p class="modal_content_date">
                        <span>Začetek:</span>
                        {{ formatDateAndTime(selectedEvent.start_time) }}
                    </p>
                    <p class="modal_content_date">
                        <span>Konec:</span>
                        {{ formatDateAndTime(selectedEvent.end_time) }}
                    </p>
                    <p
                        v-if="selectedEvent.description"
                        class="modal_content_description"
                    >
                        <span>Opis:</span>
                        {{ selectedEvent.description }}
                    </p>
                </div>
            </Modal>
        </div>
    </div>
</template>

<script>
import Modal from "./Modal.vue";

export default {
    components: {
        Modal,
    },
    data() {
        return {
            events: [],
            isAuthenticated: false,
            loading: true,
            showModal: false,
            selectedEvent: null,
        };
    },
    methods: {
        async login() {
            this.loading = true;
            const response = await fetch("/api/login");
            const data = await response.json();
            window.location.href = data.auth_url;
            this.loading = false;
        },
        async fetchEvents() {
            this.loading = true;
            const response = await fetch("/api/events");
            if (response.status === 200) {
                const events = await response.json();
                this.events = events.map((event) => ({
                    ...event,
                    showDescription: false,
                }));
                this.isAuthenticated = true;
            } else {
                this.isAuthenticated = false;
            }
            this.loading = false;
        },
        async logout() {
            this.loading = true;
            const response = await fetch("/api/logout", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            });
            if (response.status === 200) {
                this.isAuthenticated = false;
                this.events = [];
            }
            this.loading = false;
        },
        toggleDescription(event) {
            this.selectedEvent = event;
            this.showModal = true;
        },
        formatDateAndTime(date) {
            const options = {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            };
            return new Date(date)
                .toLocaleDateString("en-GB", options)
                .replace(/[/]/g, ".")
                .replace(",", "");
        },
        formatDate(date) {
            const options = {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            };
            return new Date(date)
                .toLocaleDateString("en-GB", options)
                .replace(/[/]/g, ".");
        },
    },
    async created() {
        this.loading = true;
        await this.fetchEvents();
        this.loading = false;
    },
};
</script>
