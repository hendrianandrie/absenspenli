import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Platform } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient from '../config/api';

// Konfigurasi agar notifikasi selalu muncul sebagai banner & bunyi meskipun aplikasi sedang dibuka
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

/**
 * Daftarkan perangkat untuk menerima Push Notification dari server
 */
export async function registerForPushNotificationsAsync() {
  let token = null;

  if (Platform.OS === 'android') {
    await Notifications.setNotificationChannelAsync('default', {
      name: 'Notifikasi SMPN 5 Ciamis',
      importance: Notifications.AndroidImportance.MAX,
      vibrationPattern: [0, 250, 250, 250],
      lightColor: '#0284c7',
      sound: 'default',
    });
  }

  // Hanya daftarkan jika dijalankan di perangkat fisik asli (bukan simulator/web)
  if (Device.isDevice) {
    const { status: existingStatus } = await Notifications.getPermissionsAsync();
    let finalStatus = existingStatus;

    if (existingStatus !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      finalStatus = status;
    }

    if (finalStatus !== 'granted') {
      console.log('Izin push notification tidak diberikan oleh pengguna.');
      return null;
    }

    try {
      const pushTokenData = await Notifications.getExpoPushTokenAsync();
      token = pushTokenData.data;

      // Kirim token ke backend Laravel
      if (token) {
        await apiClient.post('/device-token/register', {
          expo_push_token: token,
          device_name: `${Device.brand || ''} ${Device.modelName || 'Mobile Device'}`.trim(),
        });
        await AsyncStorage.setItem('expo_push_token', token);
        console.log('✓ Expo Push Token berhasil didaftarkan:', token);
      }
    } catch (error) {
      console.warn('Gagal mendapatkan / mendaftarkan push token:', error.message);
    }
  } else {
    console.log('Push notification memerlukan perangkat fisik nyata.');
  }

  return token;
}

/**
 * Hapus pendaftaran push token saat logout
 */
export async function unregisterPushNotificationsAsync() {
  try {
    const storedToken = await AsyncStorage.getItem('expo_push_token');
    if (storedToken) {
      await apiClient.post('/device-token/unregister', {
        expo_push_token: storedToken,
      });
      await AsyncStorage.removeItem('expo_push_token');
      console.log('✓ Expo Push Token berhasil dihapus dari server.');
    }
  } catch (error) {
    console.warn('Gagal unregister push token:', error.message);
  }
}
