import React, { useContext } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TouchableOpacity,
  ScrollView,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { AuthContext } from '../../context/AuthContext';

export default function StaffDashboardScreen({ navigation }) {
  const { user, logout } = useContext(AuthContext);

  const isWali = user?.is_wali_kelas;
  const canAccessScanner = user?.role === 'admin' || user?.role === 'piket';
  const kelasBinaan = user?.kelas_binaan;

  const navigateToWeb = (title, path) => {
    navigation.navigate('StaffWeb', { title, path });
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
      {/* Header Profile Hero Card */}
      <View style={styles.headerCard}>
        {/* Decorative Circle Ornaments */}
        <View style={styles.heroCircle1} pointerEvents="none" />
        <View style={styles.heroCircle2} pointerEvents="none" />

        <View style={styles.avatarCircle}>
          <Text style={styles.avatarText}>
            {user?.name ? user.name.charAt(0).toUpperCase() : 'G'}
          </Text>
        </View>
        <Text style={styles.nameText}>{user?.name}</Text>
        <View style={styles.roleBadge}>
          <Ionicons
            name={user?.role === 'admin' ? 'shield-checkmark' : (isWali ? 'school' : 'book')}
            size={13}
            color="#0284c7"
          />
          <Text style={styles.roleBadgeText}>
            {user?.role === 'admin'
              ? 'Administrator'
              : isWali
              ? `Guru & Wali Kelas ${kelasBinaan || ''}`
              : 'Guru Mata Pelajaran'}
          </Text>
        </View>

        <Text style={styles.nipText}>
          {user?.nip ? `NIP. ${user.nip}` : `ID: ${user?.username || '-'}`}
        </Text>
      </View>

      {/* SECTION 1: Menu Penilaian Guru (Semua Guru) */}
      <View style={styles.sectionWrap}>
        <View style={styles.sectionHeader}>
          <Ionicons name="ribbon-outline" size={18} color="#0284c7" />
          <Text style={styles.sectionTitle}>Penilaian Siswa</Text>
        </View>

        {/* 1. Tambah / Input Nilai Baru */}
        <TouchableOpacity
          style={styles.menuCard}
          activeOpacity={0.7}
          onPress={() => navigateToWeb('Input Nilai Baru', '/nilai/kegiatan/create')}
        >
          <View style={[styles.menuIconWrap, { backgroundColor: '#e0f2fe' }]}>
            <Ionicons name="add-circle" size={24} color="#0284c7" />
          </View>
          <View style={styles.menuInfo}>
            <Text style={styles.menuTitle}>Input Nilai Baru</Text>
            <Text style={styles.menuSubtitle}>
              Tambah kegiatan & input nilai tugas, UH, UTS, UAS
            </Text>
          </View>
          <Ionicons name="chevron-forward" size={18} color="#94a3b8" />
        </TouchableOpacity>

        {/* 2. Manajemen Nilai */}
        <TouchableOpacity
          style={styles.menuCard}
          activeOpacity={0.7}
          onPress={() => navigateToWeb('Manajemen Nilai', '/nilai')}
        >
          <View style={[styles.menuIconWrap, { backgroundColor: '#ede9fe' }]}>
            <Ionicons name="file-tray-full" size={22} color="#7c3aed" />
          </View>
          <View style={styles.menuInfo}>
            <Text style={styles.menuTitle}>Manajemen Nilai</Text>
            <Text style={styles.menuSubtitle}>
              Daftar nilai, rekap rata-rata, & cetak rapor siswa
            </Text>
          </View>
          <Ionicons name="chevron-forward" size={18} color="#94a3b8" />
        </TouchableOpacity>
      </View>

      {/* SECTION 2: Menu Khusus Wali Kelas (Hanya untuk Wali Kelas) */}
      {isWali && (
        <View style={styles.sectionWrap}>
          <View style={styles.sectionHeader}>
            <Ionicons name="school-outline" size={18} color="#059669" />
            <Text style={styles.sectionTitle}>
              Kelas Binaan {kelasBinaan ? `(${kelasBinaan})` : ''}
            </Text>
          </View>

          {/* 3. Rekap Absen Kelas Binaan */}
          <TouchableOpacity
            style={styles.menuCard}
            activeOpacity={0.7}
            onPress={() => navigateToWeb('Rekap Absen Kelas Binaan', '/wali-kelas/kehadiran')}
          >
            <View style={[styles.menuIconWrap, { backgroundColor: '#d1fae5' }]}>
              <Ionicons name="calendar" size={22} color="#059669" />
            </View>
            <View style={styles.menuInfo}>
              <Text style={styles.menuTitle}>Rekap Absen Kelas Binaan</Text>
              <Text style={styles.menuSubtitle}>
                Monitoring kehadiran harian & persentase hadir
              </Text>
            </View>
            <Ionicons name="chevron-forward" size={18} color="#94a3b8" />
          </TouchableOpacity>

          {/* 4. Rekap Nilai Kelas Binaan */}
          <TouchableOpacity
            style={styles.menuCard}
            activeOpacity={0.7}
            onPress={() => navigateToWeb('Rekap Nilai Kelas Binaan', '/wali-kelas/binaan')}
          >
            <View style={[styles.menuIconWrap, { backgroundColor: '#fef3c7' }]}>
              <Ionicons name="bar-chart" size={22} color="#d97706" />
            </View>
            <View style={styles.menuInfo}>
              <Text style={styles.menuTitle}>Rekap Nilai Kelas Binaan</Text>
              <Text style={styles.menuSubtitle}>
                Leger nilai seluruh mata pelajaran siswa binaan
              </Text>
            </View>
            <Ionicons name="chevron-forward" size={18} color="#94a3b8" />
          </TouchableOpacity>
        </View>
      )}

      {/* Scanner Presensi Wajah (Khusus Akun Admin / Piket) */}
      {canAccessScanner && (
        <View style={styles.sectionWrap}>
          <TouchableOpacity
            style={styles.scannerBtn}
            onPress={() => navigation.navigate('FaceScanner')}
            activeOpacity={0.8}
          >
            <Ionicons name="camera" size={20} color="#ffffff" />
            <Text style={styles.scannerBtnText}>Buka Scanner Presensi Wajah</Text>
            <Ionicons name="arrow-forward" size={16} color="#ffffff" />
          </TouchableOpacity>
        </View>
      )}

      {/* Info Notice Box */}
      <View style={styles.noticeCard}>
        <Image
          source={require('../../../assets/logo.png')}
          style={{ width: 34, height: 34, borderRadius: 17 }}
          resizeMode="contain"
        />
        <View style={styles.noticeContent}>
          <Text style={styles.noticeTitle}>SI-KASEP — SMPN 5 Ciamis</Text>
          <Text style={styles.noticeDesc}>
            Fitur penilaian dan rekap kelas telah terintegrasi penuh secara realtime antara aplikasi mobile dan portal web.
          </Text>
        </View>
      </View>

      {/* Tombol Logout */}
      <TouchableOpacity style={styles.logoutBtn} onPress={logout} activeOpacity={0.8}>
        <Ionicons name="log-out-outline" size={18} color="#dc2626" />
        <Text style={styles.logoutText}>Keluar dari Aplikasi</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f1f5f9',
  },
  content: {
    padding: 16,
    paddingBottom: 36,
  },
  headerCard: {
    backgroundColor: '#0284c7',
    borderRadius: 22,
    padding: 22,
    alignItems: 'center',
    marginBottom: 16,
    position: 'relative',
    overflow: 'hidden',
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.22,
    shadowRadius: 10,
    elevation: 4,
  },
  heroCircle1: {
    position: 'absolute',
    top: -30,
    right: -30,
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: 'rgba(255, 255, 255, 0.12)',
  },
  heroCircle2: {
    position: 'absolute',
    bottom: -40,
    left: -40,
    width: 140,
    height: 140,
    borderRadius: 70,
    backgroundColor: 'rgba(255, 255, 255, 0.08)',
  },
  avatarCircle: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#ffffff',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 4,
    elevation: 3,
  },
  avatarText: {
    fontSize: 24,
    fontWeight: '800',
    color: '#0284c7',
  },
  nameText: {
    fontSize: 17,
    fontWeight: '800',
    color: '#ffffff',
    textAlign: 'center',
  },
  roleBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 20,
    marginTop: 8,
    gap: 5,
  },
  roleBadgeText: {
    fontSize: 11.5,
    fontWeight: '700',
    color: '#0369a1',
  },
  nipText: {
    fontSize: 11,
    color: '#bae6fd',
    marginTop: 6,
    fontWeight: '500',
  },
  sectionWrap: {
    marginBottom: 16,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 10,
    paddingHorizontal: 2,
  },
  sectionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#334155',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  menuCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 14,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    shadowColor: '#0f172a',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.04,
    shadowRadius: 4,
    elevation: 2,
  },
  menuIconWrap: {
    width: 44,
    height: 44,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  menuInfo: {
    flex: 1,
  },
  menuTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: '#0f172a',
  },
  menuSubtitle: {
    fontSize: 11.5,
    color: '#64748b',
    marginTop: 2,
    lineHeight: 16,
  },
  scannerBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#0284c7',
    paddingVertical: 14,
    borderRadius: 14,
    gap: 8,
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 3,
  },
  scannerBtnText: {
    fontSize: 13.5,
    fontWeight: '700',
    color: '#ffffff',
  },
  noticeCard: {
    flexDirection: 'row',
    backgroundColor: '#f0f9ff',
    borderWidth: 1,
    borderColor: '#bae6fd',
    borderRadius: 14,
    padding: 12,
    marginBottom: 16,
    alignItems: 'center',
    gap: 10,
  },
  noticeContent: {
    flex: 1,
  },
  noticeTitle: {
    fontSize: 12,
    fontWeight: '700',
    color: '#0369a1',
  },
  noticeDesc: {
    fontSize: 11,
    color: '#0c4a6e',
    marginTop: 2,
    lineHeight: 15,
  },
  logoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fee2e2',
    borderWidth: 1,
    borderColor: '#fecaca',
    paddingVertical: 12,
    borderRadius: 12,
    gap: 6,
  },
  logoutText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#dc2626',
  },
});
