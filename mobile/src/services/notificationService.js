import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import Constants from 'expo-constants';
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

  try {
    if (Platform.OS === 'android') {
      try {
        await Notifications.setNotificationChannelAsync('default', {
          name: 'Notifikasi SMPN 5 Ciamis',
          importance: Notifications.AndroidImportance.MAX,
          vibrationPattern: [0, 250, 250, 250],
          lightColor: '#0284c7',
          sound: 'default',
        });
      } catch (channelErr) {
        console.warn('NotificationChannel skipped in Expo Go:', channelErr.message);
      }
    }

    // Periksa izin notifikasi
    try {
      const { status: existingStatus } = await Notifications.getPermissionsAsync();
      if (existingStatus !== 'granted') {
        await Notifications.requestPermissionsAsync();
      }
    } catch (permErr) {
      console.warn('Permissions check skipped in Expo Go:', permErr.message);
    }

    try {
      const pushTokenData = await Notifications.getExpoPushTokenAsync();
      token = pushTokenData?.data;
    } catch (tokenErr) {
      console.warn('Gagal getExpoPushTokenAsync:', tokenErr.message);
      // Fallback unik untuk Expo Go simulator / mode dev
      const deviceId = Device.osBuildId || Device.modelName || 'device';
      token = `ExpoDevToken-${deviceId}-${Date.now()}`;
    }

    // Kirim token ke backend Laravel
    if (token) {
      const deviceName = `${Device.brand || ''} ${Device.modelName || 'Mobile Device'}`.trim();
      const res = await apiClient.post('/device-token/register', {
        expo_push_token: token,
        device_name: deviceName,
      });

      await AsyncStorage.setItem('expo_push_token', token);
      console.log('✓ Expo Push Token berhasil didaftarkan:', token);

      return {
        success: true,
        token: token,
        data: res.data,
      };
    }

    return {
      success: false,
      message: 'Tidak dapat memperoleh token perangkat.',
    };
  } catch (error) {
    console.warn('Gagal mendaftarkan perangkat:', error);
    return {
      success: false,
      message: error.response?.data?.message || error.message || 'Gagal mendaftarkan perangkat ke server.',
    };
  }
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
