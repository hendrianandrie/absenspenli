import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Default base URL: Menggunakan Server Produksi Resmi SMPN 5 Ciamis
export const DEFAULT_BASE_URL = 'https://smpn5ciamis.sch.id/api/v1';

// Fungsi pembantu untuk memastikan format Base URL selalu lengkap dengan /api/v1
export function normalizeBaseUrl(url) {
  if (!url || typeof url !== 'string') return DEFAULT_BASE_URL;
  let trimmed = url.trim().replace(/\/+$/, '');
  
  if (!trimmed.startsWith('http://') && !trimmed.startsWith('https://')) {
    trimmed = `https://${trimmed}`;
  }
  
  if (!trimmed.includes('/api/v1')) {
    if (trimmed.endsWith('/api')) {
      trimmed = `${trimmed}/v1`;
    } else {
      trimmed = `${trimmed}/api/v1`;
    }
  }
  return trimmed;
}

export function getRootWebUrl(url) {
  const norm = normalizeBaseUrl(url);
  return norm.replace('/api/v1', '').replace('/api', '');
}

const apiClient = axios.create({
  baseURL: DEFAULT_BASE_URL,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor untuk menyematkan Bearer Token di setiap request
apiClient.interceptors.request.use(
  async (config) => {
    try {
      // Periksa apakah pengguna mengganti Base URL di pengaturan
      const customBaseUrl = await AsyncStorage.getItem('custom_base_url');
      if (customBaseUrl) {
        config.baseURL = normalizeBaseUrl(customBaseUrl);
      } else {
        config.baseURL = DEFAULT_BASE_URL;
      }

      const token = await AsyncStorage.getItem('auth_token');
      if (token) {
        config.headers = config.headers || {};
        config.headers.Authorization = `Bearer ${token}`;
        config.headers['Authorization'] = `Bearer ${token}`;
        if (typeof config.headers.set === 'function') {
          config.headers.set('Authorization', `Bearer ${token}`);
        }
      }
    } catch (e) {
      console.warn('Gagal membaca token:', e);
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Interceptor Response untuk menangani error sesi kedaluwarsa (401)
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response && error.response.status === 401) {
      console.warn('Sesi login kedaluwarsa (401). Token lama tidak berlaku di server ini.');
    }
    return Promise.reject(error);
  }
);

export default apiClient;
