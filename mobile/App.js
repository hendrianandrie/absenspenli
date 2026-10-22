import React, { useEffect, useRef } from 'react';
import { StatusBar } from 'expo-status-bar';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { NavigationContainer } from '@react-navigation/native';
import * as Notifications from 'expo-notifications';
import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient from './src/config/api';
import { AuthProvider } from './src/context/AuthContext';
import AppNavigator from './src/navigation/AppNavigator';

export default function App() {
  const navigationRef = useRef(null);
  const notificationListener = useRef();
  const responseListener = useRef();

  useEffect(() => {
    try {
      // Listener saat notifikasi diterima ketika aplikasi sedang aktif di layar
      notificationListener.current = Notifications.addNotificationReceivedListener(notification => {
        console.log('Notifikasi diterima saat aktif:', notification.request.content.title);
      });

      // Listener saat pengguna mengetuk (tap) notifikasi di status bar / lockscreen
      responseListener.current = Notifications.addNotificationResponseReceivedListener(response => {
        const data = response.notification?.request?.content?.data;
        console.log('Pengguna mengetuk notifikasi dengan data:', data);

        if (navigationRef.current && data) {
          if (data.type === 'absen') {
            navigationRef.current.navigate('SiswaAbsen');
          } else if (data.type === 'nilai') {
            navigationRef.current.navigate('SiswaNilai');
          }
        }
      });
    } catch (e) {
      console.warn('Notification listeners bypassed in Expo Go:', e.message);
    }

    // Active Real-Time Notification Watcher (Memastikan notifikasi selalu muncul di Expo Go maupun APK)
    const pollInterval = setInterval(async () => {
      try {
        const authToken = await AsyncStorage.getItem('auth_token');
        if (!authToken) return;

        const res = await apiClient.get('/device-token/latest');
        const notif = res.data?.data;
        if (notif && notif.id) {
          const lastShownId = await AsyncStorage.getItem('last_shown_notif_id');
          if (lastShownId !== notif.id) {
            await AsyncStorage.setItem('last_shown_notif_id', notif.id);
            await Notifications.scheduleNotificationAsync({
              content: {
                title: notif.title,
                body: notif.body,
                data: notif.data,
                sound: 'default',
              },
              trigger: null,
            });
          }
        }
      } catch (err) {
        // silent fail on network errors
      }
    }, 5000);

    return () => {
      clearInterval(pollInterval);
      try {
        if (notificationListener.current) {
          notificationListener.current.remove();
        }
        if (responseListener.current) {
          responseListener.current.remove();
        }
      } catch (e) {}
    };
  }, []);

  return (
    <SafeAreaProvider>
      <AuthProvider>
        <NavigationContainer ref={navigationRef}>
          <StatusBar style="light" backgroundColor="#0284c7" />
          <AppNavigator />
        </NavigationContainer>
      </AuthProvider>
    </SafeAreaProvider>
  );
}

