import { defineStore } from 'pinia';
import axios from 'axios';
import { jwtDecode } from "jwt-decode";

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || null,
        user: null,
        role: null
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.role === 'admin'
    },
    actions: {
        async login(email, password) {
            // Petición al Microservicio 1 (Python Auth)
            const response = await axios.post('http://localhost:8000/login', {
                email,
                password
            });

            this.token = response.data.access_token;
            localStorage.setItem('token', this.token);

            // se lee el rol del tooken
            const decoded = jwtDecode(this.token);
            this.role = decoded.role;
            this.user = decoded.sub;
        },
        logout() {
            this.token = null;
            this.user = null;
            this.role = null;
            localStorage.removeItem('token');
        },
        initialize() {
            if (this.token) {
                try {
                    const decoded = jwtDecode(this.token);
                    this.role = decoded.role;
                    this.user = decoded.sub;
                } catch {
                    this.logout();
                }
            }
        }
    }
});