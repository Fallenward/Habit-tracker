import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
const cleanApiUrl = API_URL.replace(/\/$/, '');

const api = axios.create({
  baseURL: cleanApiUrl,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
});

// Add token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle response errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Auth API
export const authAPI = {
  register: (data) => api.post('/register', data),
  login: (data) => api.post('/login', data),
  logout: () => api.post('/logout'),
  getUser: () => api.get('/v1/user'),
};

// Habits API
export const habitsAPI = {
  getAll: () => api.get('/v1/habits'),
  getToday: () => api.get('/v1/habits/today'),
  getOne: (id) => api.get(`/v1/habits/${id}`),
  create: (data) => api.post('/v1/habits', data),
  update: (id, data) => api.put(`/v1/habits/${id}`, data),
  delete: (id) => api.delete(`/v1/habits/${id}`),
};

// Logs API
export const logsAPI = {
  update: (date, data) => api.put(`/v1/logs/${date}`, data),
};

// Calendar API
export const calendarAPI = {
  getMonth: (month) => api.get('/v1/calendar', { params: { month } }),
  getDate: (date) => api.get(`/v1/calendar/${date}`),
};

// Stats API
export const statsAPI = {
  get: (range = '30d') => api.get('/v1/stats', { params: { range } }),
};

export default api;

