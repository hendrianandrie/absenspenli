import React from 'react';
import { View, Text, Image, StyleSheet } from 'react-native';
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
          color: '#ffffff',
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
            <View style={styles.brandTitleRow}>
              <View style={styles.logoBadge}>
                <Image
                  source={require('../../assets/logo.png')}
                  style={styles.logoImage}
                  resizeMode="contain"
                />
              </View>
              <View>
                <Text style={styles.brandTitleText}>SI-KASEP</Text>
                <Text style={styles.brandSubtitleText}>SMP NEGERI 5 CIAMIS</Text>
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
          headerShadowVisible: false,
        }}
      />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  brandTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  logoBadge: {
    width: 34,
    height: 34,
    borderRadius: 17,
    backgroundColor: '#ffffff',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 3,
    borderWidth: 1.5,
    borderColor: '#ffffff',
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 3,
  },
  logoImage: {
    width: '100%',
    height: '100%',
  },
  brandTitleText: {
    color: '#ffffff',
    fontWeight: '800',
    fontSize: 16,
    letterSpacing: 1,
    lineHeight: 18,
  },
  brandSubtitleText: {
    color: '#bae6fd',
    fontSize: 10.5,
    fontWeight: '700',
    letterSpacing: 0.5,
  },
});
