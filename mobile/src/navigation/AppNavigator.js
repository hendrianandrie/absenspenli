import React, { useContext } from 'react';
import { View, ActivityIndicator, StyleSheet, Text, Image } from 'react-native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { AuthContext } from '../context/AuthContext';

import LoginScreen from '../screens/auth/LoginScreen';
import SiswaTabNavigator from './SiswaTabNavigator';
import StaffDashboardScreen from '../screens/common/StaffDashboardScreen';
import PiketDashboardScreen from '../screens/piket/PiketDashboardScreen';
import FaceScannerScreen from '../screens/piket/FaceScannerScreen';

const Stack = createNativeStackNavigator();

export default function AppNavigator() {
  const { user, isLoading } = useContext(AuthContext);

  if (isLoading) {
    return (
      <View style={styles.splash}>
        <Image
          source={require('../../assets/logo.png')}
          style={{ width: 68, height: 68, marginBottom: 16 }}
          resizeMode="contain"
        />
        <ActivityIndicator size="large" color="#0284c7" />
        <Text style={styles.splashText}>Memuat SI-KASEP...</Text>
        <Text style={{ fontSize: 11, color: '#94a3b8', marginTop: 4, fontWeight: '600' }}>
          SMP NEGERI 5 CIAMIS
        </Text>
      </View>
    );
  }

  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      {!user ? (
        // Sesi Belum Login
        <Stack.Screen name="Login" component={LoginScreen} />
      ) : user.role === 'siswa' ? (
        // Navigasi Khusus Siswa
        <Stack.Screen name="SiswaHome" component={SiswaTabNavigator} />
      ) : user.role === 'piket' ? (
        // Navigasi Khusus Petugas Piket (Fokus Presensi Wajah Kamera)
        <>
          <Stack.Screen name="PiketHome" component={PiketDashboardScreen} />
          <Stack.Screen name="FaceScanner" component={FaceScannerScreen} />
        </>
      ) : (
        // Navigasi Pendidik / Guru / Admin
        <>
          <Stack.Screen
            name="StaffHome"
            component={StaffDashboardScreen}
            options={{
              headerShown: true,
              title: 'Portal Guru & Staf',
              headerStyle: { backgroundColor: '#0284c7' },
              headerTintColor: '#ffffff',
            }}
          />
          <Stack.Screen name="FaceScanner" component={FaceScannerScreen} />
        </>
      )}
    </Stack.Navigator>
  );
}

const styles = StyleSheet.create({
  splash: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f8fafc',
  },
  splashText: {
    marginTop: 12,
    fontSize: 14,
    color: '#64748b',
    fontWeight: '600',
  },
});
