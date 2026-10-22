import React from 'react';
import { View, Text, Image } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';

import SiswaDashboardScreen from '../screens/siswa/SiswaDashboardScreen';
import SiswaAbsenScreen from '../screens/siswa/SiswaAbsenScreen';
import SiswaNilaiScreen from '../screens/siswa/SiswaNilaiScreen';
import ProfileScreen from '../screens/common/ProfileScreen';

const Tab = createBottomTabNavigator();

export default function SiswaTabNavigator() {
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerStyle: {
          backgroundColor: '#0284c7',
        },
        headerTintColor: '#ffffff',
        headerTitleStyle: {
          fontWeight: '700',
          fontSize: 16,
        },
        tabBarActiveTintColor: '#0284c7',
        tabBarInactiveTintColor: '#94a3b8',
        tabBarStyle: {
          backgroundColor: '#ffffff',
          borderTopColor: '#e2e8f0',
          paddingBottom: 6,
          paddingTop: 6,
          height: 60,
        },
        tabBarLabelStyle: {
          fontSize: 10.5,
          fontWeight: '600',
        },
        tabBarIcon: ({ focused, color, size }) => {
          let iconName;

          if (route.name === 'DashboardTab') {
            iconName = focused ? 'home' : 'home-outline';
          } else if (route.name === 'RekapAbsenTab') {
            iconName = focused ? 'calendar' : 'calendar-outline';
          } else if (route.name === 'RekapNilaiTab') {
            iconName = focused ? 'ribbon' : 'ribbon-outline';
          } else if (route.name === 'ProfileTab') {
            iconName = focused ? 'person' : 'person-outline';
          }

          return <Ionicons name={iconName} size={22} color={color} />;
        },
      })}
    >
      <Tab.Screen
        name="DashboardTab"
        component={SiswaDashboardScreen}
        options={{
          title: 'Beranda',
          headerTitle: () => (
            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
              <Image
                source={require('../../assets/logo.png')}
                style={{ width: 30, height: 30, borderRadius: 15, backgroundColor: '#ffffff' }}
                resizeMode="contain"
              />
              <View>
                <Text style={{ color: '#ffffff', fontWeight: '800', fontSize: 15, lineHeight: 17 }}>
                  SI-KASEP
                </Text>
                <Text style={{ color: '#e0f2fe', fontSize: 10, fontWeight: '600' }}>
                  SMPN 5 CIAMIS
                </Text>
              </View>
            </View>
          ),
        }}
      />
      <Tab.Screen
        name="RekapAbsenTab"
        component={SiswaAbsenScreen}
        options={{
          title: 'Rekap Absen',
          headerTitle: 'Riwayat Kehadiran Siswa',
        }}
      />
      <Tab.Screen
        name="RekapNilaiTab"
        component={SiswaNilaiScreen}
        options={{
          title: 'Rekap Nilai',
          headerTitle: 'Capaian Nilai Siswa',
        }}
      />
      <Tab.Screen
        name="ProfileTab"
        component={ProfileScreen}
        options={{
          title: 'Profil',
          headerTitle: 'Profil Pengguna',
        }}
      />
    </Tab.Navigator>
  );
}
