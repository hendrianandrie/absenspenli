import React, { createContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient from '../config/api';
import { registerForPushNotificationsAsync, unregisterPushNotificationsAsync } from '../services/notificationService';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  // Cek apakah ada sesi login tersimpan saat aplikasi pertama kali dibuka
  useEffect(() => {
    const loadStorageData = async () => {
      try {
        const storedToken = await AsyncStorage.getItem('auth_token');
        const storedUser = await AsyncStorage.getItem('auth_user');

        if (storedToken && storedUser) {
          setToken(storedToken);
          setUser(JSON.parse(storedUser));
          // Daftarkan ulang push token jika belum atau terbarui
          registerForPushNotificationsAsync().catch(err => console.log('Push register bg error:', err));
        }
      } catch (e) {
        console.warn('Gagal membaca sesi lokal:', e);
      } finally {
        setIsLoading(false);
      }
    };

    loadStorageData();
  }, []);

  const login = async (loginInput, password) => {
    try {
      const response = await apiClient.post('/login', {
        login: loginInput.trim(),
        password: password,
      });

      if (response.data && response.data.status === 'success') {
        const authToken = response.data.token;
        const authUser = response.data.user;

        await AsyncStorage.setItem('auth_token', authToken);
        await AsyncStorage.setItem('auth_user', JSON.stringify(authUser));

        setToken(authToken);
        setUser(authUser);

        // Daftarkan perangkat untuk push notifications
        registerForPushNotificationsAsync().catch(err => console.log('Push register error:', err));

        return { success: true, user: authUser };
      } else {
        return {
          success: false,
          message: response.data.message || 'Login gagal, periksa NIS/Password Anda.',
        };
      }
    } catch (error) {
      console.warn('Login error details:', error.message, error.response?.status, error.response?.data);
      let msg = error.response?.data?.message;
      if (!msg) {
        if (error.response) {
          msg = `Server merespon error HTTP ${error.response.status}: ${typeof error.response.data === 'string' ? error.response.data.substring(0, 100) : JSON.stringify(error.response.data)}`;
        } else if (error.code === 'ECONNABORTED' || error.message?.includes('timeout')) {
          msg = 'Koneksi timeout. Server terlalu lambat merespon.';
        } else {
          msg = `Gagal terhubung ke server (${error.message || 'Network Error'}). Silakan periksa Alamat Server di bawah.`;
        }
      }
      return { success: false, message: msg };
    }
  };

  const logout = async () => {
    try {
      await unregisterPushNotificationsAsync();
      await apiClient.post('/logout');
    } catch (e) {
      // Abaikan error jaringan saat logout
    } finally {
      await AsyncStorage.removeItem('auth_token');
      await AsyncStorage.removeItem('auth_user');
      setToken(null);
      setUser(null);
    }
  };

  return (
    <AuthContext.Provider value={{ user, token, isLoading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};
