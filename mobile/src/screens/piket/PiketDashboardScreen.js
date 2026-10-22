import React, { useContext } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TouchableOpacity,
  ScrollView,
  Alert,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { StatusBar } from 'expo-status-bar';
import { AuthContext } from '../../context/AuthContext';

export default function PiketDashboardScreen({ navigation }) {
  const { user, logout } = useContext(AuthContext);

  const handleLogout = () => {
    Alert.alert(
      'Konfirmasi Keluar',
      'Apakah Anda yakin ingin keluar dari akun Petugas Piket?',
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Keluar',
          style: 'destructive',
          onPress: () => logout(),
        },
      ]
    );
  };

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={styles.scrollContent}
      showsVerticalScrollIndicator={false}
      bounces={false}
    >
      <StatusBar style="light" />

      {/* Top Banner Melengkung dengan Ornamen Lingkaran (Tema Resmi SI-KASEP) */}
      <View style={styles.topBanner}>
        <View style={styles.decorCircle1} />
        <View style={styles.decorCircle2} />
        <View style={styles.decorCircle3} />

        {/* Avatar Petugas Piket */}
        <View style={styles.avatarContainer}>
          <View style={styles.avatar}>
            <Ionicons name="camera-outline" size={38} color="#0284c7" />
          </View>
        </View>

        <Text style={styles.userName}>{user?.name || 'Petugas Piket'}</Text>
        <View style={styles.roleBadge}>
          <Text style={styles.roleText}>PETUGAS PIKET • PRESENSI WAJAH</Text>
        </View>
      </View>

      <View style={styles.cardContainer}>
        {/* Hero Card: Tombol Utama Buka Scanner Wajah */}
        <View style={styles.scannerHeroCard}>
          <View style={styles.scannerIconWrap}>
            <Ionicons name="scan-circle" size={48} color="#0284c7" />
          </View>
          <Text style={styles.scannerHeroTitle}>Presensi Wajah Siswa</Text>
          <Text style={styles.scannerHeroDesc}>
            Pindai wajah siswa menggunakan kamera HP untuk mencatat kehadiran otomatis secara instan.
          </Text>

          <TouchableOpacity
            style={styles.startScanBtn}
            onPress={() => navigation.navigate('FaceScanner')}
            activeOpacity={0.8}
          >
            <Ionicons name="camera" size={20} color="#ffffff" />
            <Text style={styles.startScanBtnText}>Buka Kamera Scanner</Text>
            <Ionicons name="arrow-forward" size={18} color="#ffffff" />
          </TouchableOpacity>
        </View>

        {/* Petunjuk Penggunaan */}
        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.cardHeaderIconBox}>
              <Ionicons name="information-circle-outline" size={17} color="#0284c7" />
            </View>
            <Text style={styles.cardTitle}>Panduan Petugas Piket</Text>
          </View>

          <View style={styles.guideStep}>
            <View style={styles.stepNum}>
              <Text style={styles.stepNumText}>1</Text>
            </View>
            <Text style={styles.guideText}>
              Tekan tombol <Text style={{ fontWeight: '700' }}>"Buka Kamera Scanner"</Text> di atas.
            </Text>
          </View>

          <View style={styles.guideStep}>
            <View style={styles.stepNum}>
              <Text style={styles.stepNumText}>2</Text>
            </View>
            <Text style={styles.guideText}>
              Arahkan kamera ke wajah siswa yang hadir di gerbang / sekolah.
            </Text>
          </View>

          <View style={styles.guideStep}>
            <View style={styles.stepNum}>
              <Text style={styles.stepNumText}>3</Text>
            </View>
            <Text style={styles.guideText}>
              Sistem AI akan mencocokkan wajah siswa, membunyikan notifikasi, dan mencatat status <Text style={{ fontWeight: '700', color: '#16a34a' }}>Hadir</Text> secara otomatis.
            </Text>
          </View>
        </View>

        {/* Informasi Akun */}
        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.cardHeaderIconBox}>
              <Ionicons name="id-card-outline" size={16} color="#0284c7" />
            </View>
            <Text style={styles.cardTitle}>Informasi Akun Piket</Text>
          </View>

          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Username:</Text>
            <Text style={styles.infoValue}>{user?.username || 'piket'}</Text>
          </View>
          <View style={[styles.infoRow, { borderBottomWidth: 0 }]}>
            <Text style={styles.infoLabel}>Peran:</Text>
            <Text style={[styles.infoValue, { color: '#0284c7' }]}>Petugas Piket Sekolah</Text>
          </View>
        </View>

        {/* Logout Button */}
        <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout} activeOpacity={0.8}>
          <Ionicons name="log-out-outline" size={20} color="#dc2626" />
          <Text style={styles.logoutBtnText}>Keluar dari Akun Piket</Text>
        </TouchableOpacity>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f1f5f9',
  },
  scrollContent: {
    flexGrow: 1,
    paddingBottom: 32,
  },
  topBanner: {
    backgroundColor: '#0284c7',
    paddingTop: 44,
    paddingBottom: 48,
    borderBottomLeftRadius: 36,
    borderBottomRightRadius: 36,
    alignItems: 'center',
    position: 'relative',
    overflow: 'hidden',
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.22,
    shadowRadius: 10,
    elevation: 5,
  },
  decorCircle1: {
    position: 'absolute',
    top: -30,
    right: -30,
    width: 150,
    height: 150,
    borderRadius: 75,
    backgroundColor: 'rgba(255, 255, 255, 0.12)',
  },
  decorCircle2: {
    position: 'absolute',
    bottom: -20,
    left: -20,
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: 'rgba(255, 255, 255, 0.08)',
  },
  decorCircle3: {
    position: 'absolute',
    top: 30,
    left: 20,
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: 'rgba(186, 230, 253, 0.15)',
  },
  avatarContainer: {
    marginBottom: 10,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.16,
    shadowRadius: 8,
    elevation: 6,
  },
  avatar: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#ffffff',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: '#e0f2fe',
  },
  userName: {
    fontSize: 19,
    fontWeight: '800',
    color: '#ffffff',
    textAlign: 'center',
    letterSpacing: 0.3,
  },
  roleBadge: {
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    paddingHorizontal: 14,
    paddingVertical: 4,
    borderRadius: 16,
    marginTop: 8,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.35)',
  },
  roleText: {
    fontSize: 11,
    fontWeight: '700',
    color: '#ffffff',
    letterSpacing: 0.8,
  },
  cardContainer: {
    paddingHorizontal: 18,
    marginTop: -22,
    zIndex: 2,
  },
  scannerHeroCard: {
    backgroundColor: '#ffffff',
    borderRadius: 22,
    padding: 24,
    alignItems: 'center',
    marginBottom: 16,
    borderWidth: 1.5,
    borderColor: '#bae6fd',
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.15,
    shadowRadius: 14,
    elevation: 5,
  },
  scannerIconWrap: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: '#f0f9ff',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#e0f2fe',
  },
  scannerHeroTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: '#0f172a',
    marginBottom: 6,
  },
  scannerHeroDesc: {
    fontSize: 12,
    color: '#64748b',
    textAlign: 'center',
    lineHeight: 18,
    marginBottom: 18,
    paddingHorizontal: 10,
  },
  startScanBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#0284c7',
    paddingVertical: 14,
    paddingHorizontal: 20,
    borderRadius: 14,
    gap: 10,
    width: '100%',
    justifyContent: 'center',
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 4,
  },
  startScanBtnText: {
    color: '#ffffff',
    fontSize: 15,
    fontWeight: '700',
  },
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 20,
    padding: 20,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    shadowColor: '#0f172a',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.06,
    shadowRadius: 12,
    elevation: 3,
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
    paddingBottom: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
    gap: 8,
  },
  cardHeaderIconBox: {
    width: 28,
    height: 28,
    borderRadius: 8,
    backgroundColor: '#e0f2fe',
    justifyContent: 'center',
    alignItems: 'center',
  },
  cardTitle: {
    fontSize: 13.5,
    fontWeight: '700',
    color: '#1e293b',
    letterSpacing: 0.3,
  },
  guideStep: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    marginBottom: 12,
  },
  stepNum: {
    width: 22,
    height: 22,
    borderRadius: 11,
    backgroundColor: '#0284c7',
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 1,
  },
  stepNumText: {
    color: '#ffffff',
    fontSize: 11,
    fontWeight: '800',
  },
  guideText: {
    flex: 1,
    fontSize: 12.5,
    color: '#475569',
    lineHeight: 18,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  infoLabel: {
    fontSize: 12.5,
    color: '#64748b',
    fontWeight: '500',
  },
  infoValue: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0f172a',
  },
  logoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fee2e2',
    borderWidth: 1,
    borderColor: '#fecaca',
    paddingVertical: 14,
    borderRadius: 16,
    gap: 8,
    marginTop: 4,
    shadowColor: '#dc2626',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 6,
    elevation: 2,
  },
  logoutBtnText: {
    fontSize: 14,
    fontWeight: '700',
    color: '#dc2626',
  },
});
