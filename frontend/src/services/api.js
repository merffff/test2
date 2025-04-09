import axios from 'axios';


const API_URL = '/api';

const api = axios.create({
    baseURL: API_URL,
    withCredentials: false,
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
    console.log('Request config:', config);
    return config;
});

// Handle responses and errors
api.interceptors.response.use(
    response => {
        console.log('Response:', response);
        return response;
    },
    error => {
        console.error('API Error:', error.response || error);
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            // Don't redirect here, just let the login component handle it
        }
        return Promise.reject(error);
    }
);

// Auth services
export const login = async (email, password) => {
    try {
        console.log('Login attempt:', { email, password: '***' });
        const response = await api.post('/login', { email, password });
        console.log('Login response:', response.data);

        if (response.data.token) {
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
        }
        return response.data;
    } catch (error) {
        console.error('Login error:', error.response?.data || error.message);
        throw error;
    }
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
