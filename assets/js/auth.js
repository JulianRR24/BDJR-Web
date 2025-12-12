// assets/js/auth.js
import { apiRequest } from './api.js';

export async function login(email, password) {
    try {
        console.log("Calling auth.php...");
        const response = await apiRequest('auth.php?action=login', 'POST', { email, password });
        console.log("Auth API Response:", response);

        // Check for token in direct response OR in nested data (debug mode)
        const token = response.access_token || (response.data && response.data.access_token);
        const user = response.user || (response.data && response.data.user);

        if (token) {
            localStorage.setItem('supabase_token', token);
            localStorage.setItem('user', JSON.stringify(user));
            return user;
        } else if (response.error) {
            throw new Error(response.error_description || response.error.message || response.error);
        } else if (response.debug_supabase_response && response.debug_supabase_response.error) {
             throw new Error(response.debug_supabase_response.error_description || "Error de Supabase: " + JSON.stringify(response.debug_supabase_response));
        } else if (response.data && response.data.error) {
             throw new Error(response.data.error_description || response.data.error.message || "Error desconocido");
        } else {
            console.error("Unexpected response structure:", response);
            throw new Error("Respuesta inesperada del servidor. Revisa la consola.");
        }
    } catch (error) {
        console.error("Login function error:", error);
        throw error;
    }
}

export async function register(email, password) {
    try {
        const response = await apiRequest('auth.php?action=register', 'POST', { email, password });
        return response;
    } catch (error) {
        throw error;
    }
}

export function logout() {
    localStorage.removeItem('supabase_token');
    localStorage.removeItem('user');
    window.location.href = 'login.html';
}

export function getCurrentUser() {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
}

export function isAuthenticated() {
    return !!localStorage.getItem('supabase_token');
}
