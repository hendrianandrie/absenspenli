import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Default base URL: Menggunakan Tunnel HTTPS publik agar tembus dari HP fisik tanpa batasan Wi-Fi/Cleartext HTTP
export const DEFAULT_BASE_URL = 'https://thin-onions-sink.loca.lt/api/v1';

const apiClient = axios.create({
  baseURL: DEFAULT_BASE_URL,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Bypass-Tunnel-Reminder': 'true',
  },
});

// Interceptor untuk menyematkan Bearer Token di setiap request
apiClient.interceptors.request.use(
  async (config) => {
    try {
      // Periksa apakah pengguna mengganti Base URL di pengaturan
      const customBaseUrl = await AsyncStorage.getItem('custom_base_url');
      if (customBaseUrl) {
        config.baseURL = customBaseUrl;
      }

      const token = await AsyncStorage.getItem('auth_token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (e) {
      console.warn('Gagal membaca token:', e);
    }
    return config;
  },
  (error) => Promise.reject(error)
);

export default apiClient;
