import React, { useContext } from 'react';
import { View, ActivityIndicator, StyleSheet, Text } from 'react-native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { AuthContext } from '../context/AuthContext';

import LoginScreen from '../screens/auth/LoginScreen';
import SiswaTabNavigator from './SiswaTabNavigator';
import StaffDashboardScreen from '../screens/common/StaffDashboardScreen';

const Stack = createNativeStackNavigator();

export default function AppNavigator() {
  const { user, isLoading } = useContext(AuthContext);

  if (isLoading) {
    return (
      <View style={styles.splash}>
        <ActivityIndicator size="large" color="#0284c7" />
        <Text style={styles.splashText}>Memuat SI-KASEP...</Text>
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
      ) : (
        // Navigasi Pendidik / Staf / Admin
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
