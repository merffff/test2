import axios from 'axios';

const API_URL = 'http://localhost:86/api';

const api = axios.create({
    baseURL: API_URL,
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

// Add token to all requests if it exists
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Handle authentication errors
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

// Auth services
export const login = (email, password) => {
    return api.post('/login', { email, password })
        .then(response => {
            if (response.data.token) {
                localStorage.setItem('token', response.data.token);
                localStorage.setItem('user', JSON.stringify(response.data.user));
            }
            return response.data;
        });
};

export const logout = () => {
    return api.post('/logout')
        .then(() => {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
        });
};

export const getCurrentUser = () => {
    return JSON.parse(localStorage.getItem('user'));
};

// Address services
export const searchAddresses = (query) => {
    return api.post('/addresses/search', { query });
};

export const saveAddress = (fullAddress, addressData) => {
    return api.post('/addresses', {
        full_address: fullAddress,
        address_data: addressData
    });
};

export const getSavedAddresses = () => {
    return api.get('/addresses');
};

export default {
    login,
    logout,
    getCurrentUser,
    searchAddresses,
    saveAddress,
    getSavedAddresses
};
