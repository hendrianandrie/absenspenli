import React from 'react';
import { View, Text, Image, StyleSheet, Platform } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { StatusBar } from 'expo-status-bar';

import SiswaDashboardScreen from '../screens/siswa/SiswaDashboardScreen';
import SiswaAbsenScreen from '../screens/siswa/SiswaAbsenScreen';
import SiswaNilaiScreen from '../screens/siswa/SiswaNilaiScreen';
import ProfileScreen from '../screens/common/ProfileScreen';

const Tab = createBottomTabNavigator();

// Komponen Header Kustom dengan Gradien & Ornamen Modern
function CustomTabHeader({ route }) {
  const insets = useSafeAreaInsets();
  const topPadding = Math.max(insets.top, Platform.OS === 'android' ? 30 : 20);

  const renderContent = () => {
    switch (route.name) {
      case 'DashboardTab':
        return (
          <View style={styles.brandRow}>
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
        );
      case 'RekapAbsenTab':
        return (
          <View style={styles.pageTitleRow}>
            <View style={styles.pageIconBadge}>
              <Ionicons name="calendar" size={19} color="#ffffff" />
            </View>
            <View>
              <Text style={styles.pageTitleText}>Riwayat Kehadiran Siswa</Text>
              <Text style={styles.pageSubtitleText}>SMP NEGERI 5 CIAMIS</Text>
            </View>
          </View>
        );
      case 'RekapNilaiTab':
        return (
          <View style={styles.pageTitleRow}>
            <View style={styles.pageIconBadge}>
              <Ionicons name="ribbon" size={19} color="#ffffff" />
            </View>
            <View>
              <Text style={styles.pageTitleText}>Capaian Nilai Siswa</Text>
              <Text style={styles.pageSubtitleText}>SMP NEGERI 5 CIAMIS</Text>
            </View>
          </View>
        );
      case 'ProfileTab':
        return (
          <View style={styles.pageTitleRow}>
            <View style={styles.pageIconBadge}>
              <Ionicons name="person" size={19} color="#ffffff" />
            </View>
            <View>
              <Text style={styles.pageTitleText}>Profil Pengguna</Text>
              <Text style={styles.pageSubtitleText}>SMP NEGERI 5 CIAMIS</Text>
            </View>
          </View>
        );
      default:
        return null;
    }
  };

  return (
    <View style={[styles.customHeaderRoot, { paddingTop: topPadding }]}>
      <StatusBar style="light" />
      {/* Background Gradient Biru Akademis */}
      <LinearGradient
        colors={['#0369a1', '#0284c7', '#0ea5e9']}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={StyleSheet.absoluteFillObject}
      />
      {/* Ornamen Lingkaran Modern */}
      <View style={styles.headerBubble1} pointerEvents="none" />
      <View style={styles.headerBubble2} pointerEvents="none" />
      <View style={styles.headerBubble3} pointerEvents="none" />

      {/* Baris Konten Header */}
      <View style={styles.customHeaderBar}>
        {renderContent()}
      </View>
    </View>
  );
}

export default function SiswaTabNavigator() {
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        header: (props) => <CustomTabHeader {...props} />,
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
        }}
      />
      <Tab.Screen
        name="RekapAbsenTab"
        component={SiswaAbsenScreen}
        options={{
          title: 'Rekap Absen',
        }}
      />
      <Tab.Screen
        name="RekapNilaiTab"
        component={SiswaNilaiScreen}
        options={{
          title: 'Rekap Nilai',
        }}
      />
      <Tab.Screen
        name="ProfileTab"
        component={ProfileScreen}
        options={{
          title: 'Profil',
        }}
      />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  customHeaderRoot: {
    backgroundColor: '#0284c7',
    position: 'relative',
    overflow: 'hidden',
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.22,
    shadowRadius: 8,
    elevation: 5,
  },
  headerBubble1: {
    position: 'absolute',
    top: -24,
    right: -20,
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: 'rgba(255, 255, 255, 0.13)',
  },
  headerBubble2: {
    position: 'absolute',
    bottom: -32,
    left: 80,
    width: 90,
    height: 90,
    borderRadius: 45,
    backgroundColor: 'rgba(255, 255, 255, 0.08)',
  },
  headerBubble3: {
    position: 'absolute',
    top: 16,
    left: -15,
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: 'rgba(186, 230, 253, 0.16)',
  },
  customHeaderBar: {
    height: 56,
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
  },
  brandRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  logoBadge: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: '#ffffff',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 3,
    borderWidth: 1.5,
    borderColor: '#ffffff',
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.18,
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
    fontSize: 16.5,
    letterSpacing: 1.2,
    lineHeight: 19,
  },
  brandSubtitleText: {
    color: '#bae6fd',
    fontSize: 10.5,
    fontWeight: '700',
    letterSpacing: 0.5,
  },
  pageTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  pageIconBadge: {
    width: 36,
    height: 36,
    borderRadius: 11,
    backgroundColor: 'rgba(255, 255, 255, 0.22)',
    borderWidth: 1.2,
    borderColor: 'rgba(255, 255, 255, 0.38)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  pageTitleText: {
    color: '#ffffff',
    fontWeight: '800',
    fontSize: 16,
    letterSpacing: 0.3,
    lineHeight: 20,
  },
  pageSubtitleText: {
    color: '#bae6fd',
    fontSize: 10.5,
    fontWeight: '700',
    letterSpacing: 0.5,
  },
});
