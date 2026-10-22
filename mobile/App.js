import React, { useEffect, useRef } from 'react';
import { StatusBar } from 'expo-status-bar';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { NavigationContainer } from '@react-navigation/native';
import * as Notifications from 'expo-notifications';
import { AuthProvider } from './src/context/AuthContext';
import AppNavigator from './src/navigation/AppNavigator';

export default function App() {
  const navigationRef = useRef(null);
  const notificationListener = useRef();
  const responseListener = useRef();

  useEffect(() => {
    // Listener saat notifikasi diterima ketika aplikasi sedang aktif di layar
    notificationListener.current = Notifications.addNotificationReceivedListener(notification => {
      console.log('Notifikasi diterima saat aktif:', notification.request.content.title);
    });

    // Listener saat pengguna mengetuk (tap) notifikasi di status bar / lockscreen
    responseListener.current = Notifications.addNotificationResponseReceivedListener(response => {
      const data = response.notification.request.content.data;
      console.log('Pengguna mengetuk notifikasi dengan data:', data);

      if (navigationRef.current && data) {
        if (data.type === 'absen') {
          navigationRef.current.navigate('SiswaAbsen');
        } else if (data.type === 'nilai') {
          navigationRef.current.navigate('SiswaNilai');
        }
      }
    });

    return () => {
      if (notificationListener.current) {
        notificationListener.current.remove();
      }
      if (responseListener.current) {
        responseListener.current.remove();
      }
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

