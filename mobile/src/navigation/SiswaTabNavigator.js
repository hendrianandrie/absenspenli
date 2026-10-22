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
            <View style={styles.titleTextCol}>
              <Text style={styles.brandTitleText} numberOfLines={1}>
                SI-KASEP
              </Text>
              <Text style={styles.brandSubtitleText} numberOfLines={1}>
                SMP NEGERI 5 CIAMIS
              </Text>
            </View>
          </View>
        );
      case 'RekapAbsenTab':
        return (
          <View style={styles.pageTitleRow}>
            <View style={styles.pageIconBadge}>
              <Ionicons name="calendar-outline" size={19} color="#ffffff" />
            </View>
            <View style={styles.titleTextCol}>
              <Text style={styles.pageTitleText} numberOfLines={1}>
                Riwayat Kehadiran Siswa
              </Text>
              <Text style={styles.pageSubtitleText} numberOfLines={1}>
                SMP NEGERI 5 CIAMIS
              </Text>
            </View>
          </View>
        );
      case 'RekapNilaiTab':
        return (
          <View style={styles.pageTitleRow}>
            <View style={styles.pageIconBadge}>
              <Ionicons name="ribbon-outline" size={19} color="#ffffff" />
            </View>
            <View style={styles.titleTextCol}>
              <Text style={styles.pageTitleText} numberOfLines={1}>
                Capaian Nilai Siswa
              </Text>
              <Text style={styles.pageSubtitleText} numberOfLines={1}>
                SMP NEGERI 5 CIAMIS
              </Text>
            </View>
          </View>
        );
      case 'ProfileTab':
        return (
          <View style={styles.pageTitleRow}>
            <View style={styles.pageIconBadge}>
              <Ionicons name="person-outline" size={19} color="#ffffff" />
            </View>
            <View style={styles.titleTextCol}>
              <Text style={styles.pageTitleText} numberOfLines={1}>
                Profil Pengguna
              </Text>
              <Text style={styles.pageSubtitleText} numberOfLines={1}>
                SMP NEGERI 5 CIAMIS
              </Text>
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
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255, 255, 255, 0.15)',
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.15,
    shadowRadius: 6,
    elevation: 4,
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
    height: 58,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-start',
    paddingHorizontal: 16,
  },
  brandRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-start',
    gap: 12,
    flex: 1,
  },
  logoBadge: {
    width: 38,
    height: 38,
    borderRadius: 19,
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
  titleTextCol: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'flex-start',
  },
  brandTitleText: {
    color: '#ffffff',
    fontWeight: '800',
    fontSize: 16.5,
    letterSpacing: 0.8,
    textAlign: 'left',
    includeFontPadding: false,
  },
  brandSubtitleText: {
    color: '#bae6fd',
    fontSize: 10.5,
    fontWeight: '700',
    letterSpacing: 0.6,
    marginTop: 2,
    textAlign: 'left',
    includeFontPadding: false,
  },
  pageTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-start',
    gap: 12,
    flex: 1,
  },
  pageIconBadge: {
    width: 38,
    height: 38,
    borderRadius: 12,
    backgroundColor: 'rgba(255, 255, 255, 0.22)',
    borderWidth: 1.2,
    borderColor: 'rgba(255, 255, 255, 0.4)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  pageTitleText: {
    color: '#ffffff',
    fontWeight: '800',
    fontSize: 16,
    letterSpacing: 0.2,
    textAlign: 'left',
    includeFontPadding: false,
  },
  pageSubtitleText: {
    color: '#bae6fd',
    fontSize: 10.5,
    fontWeight: '700',
    letterSpacing: 0.6,
    marginTop: 2,
    textAlign: 'left',
    includeFontPadding: false,
  },
});
