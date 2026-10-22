import React, { useState, useEffect, useContext } from 'react';
import {
  StyleSheet,
  Text,
  View,
  ScrollView,
  RefreshControl,
  ActivityIndicator,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { AuthContext } from '../../context/AuthContext';
import apiClient from '../../config/api';

export default function SiswaDashboardScreen({ navigation }) {
  const { user } = useContext(AuthContext);

  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [dashboardData, setDashboardData] = useState(null);

  const fetchDashboard = async () => {
    try {
      const response = await apiClient.get('/siswa/dashboard');
      if (response.data && response.data.status === 'success') {
        setDashboardData(response.data.data);
      }
    } catch (error) {
      console.warn('Gagal memuat dashboard siswa:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchDashboard();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchDashboard();
  };

  const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#0284c7" />
        <Text style={styles.loadingText}>Memuat Data Dashboard...</Text>
      </View>
    );
  }

  const siswa = dashboardData?.siswa || user?.siswa;
  const waliKelas = dashboardData?.wali_kelas;
  const absen = dashboardData?.rekap_absen_bulan_ini;
  const nilaiTerbaru = dashboardData?.nilai_terbaru || [];

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={styles.scrollContent}
      showsVerticalScrollIndicator={false}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#0284c7']} />}
    >
      {/* Header Banner Profil Siswa */}
      <View style={styles.profileHeroCard}>
        {/* Ornamen Lingkaran Transparan */}
        <View style={styles.heroCircle1} pointerEvents="none" />
        <View style={styles.heroCircle2} pointerEvents="none" />

        <View style={styles.profileRow}>
          <View style={styles.avatarCircle}>
            <Text style={styles.avatarInitials}>
              {siswa?.nama ? siswa.nama.charAt(0).toUpperCase() : 'S'}
            </Text>
          </View>
          <View style={styles.profileInfo}>
            <Text style={styles.greetingText}>{getGreeting()},</Text>
            <Text style={styles.studentName} numberOfLines={1}>
              {siswa?.nama || user?.name}
            </Text>
            <View style={styles.badgeRow}>
              <View style={styles.classBadge}>
                <Text style={styles.classBadgeText}>Kelas {siswa?.kelas || '-'}</Text>
              </View>
              <Text style={styles.nisText}>NIS: {siswa?.nis || user?.username}</Text>
            </View>
          </View>
        </View>

        {/* Info Wali Kelas */}
        {waliKelas && (
          <View style={styles.waliCard}>
            <Ionicons name="person-circle-outline" size={17} color="#ffffff" />
            <Text style={styles.waliText}>
              Wali Kelas: <Text style={styles.waliName}>{waliKelas.nama}</Text>
            </Text>
          </View>
        )}
      </View>

      {/* Ringkasan Presensi Bulan Berjalan */}
      <View style={styles.sectionCard}>
        <View style={styles.sectionHeader}>
          <View>
            <Text style={styles.sectionTitle}>Kehadiran Bulan Ini</Text>
            <Text style={styles.sectionSubtitle}>{absen?.bulan || 'Bulan Berjalan'}</Text>
          </View>
          <TouchableOpacity
            style={styles.seeMoreBtn}
            onPress={() => navigation.navigate('RekapAbsenTab')}
          >
            <Text style={styles.seeMoreText}>Lihat Detail</Text>
            <Ionicons name="chevron-forward" size={14} color="#0284c7" />
          </TouchableOpacity>
        </View>

        {/* 4 Counter Kotak */}
        <View style={styles.counterGrid}>
          <View style={[styles.counterBox, { backgroundColor: '#f0fdf4', borderColor: '#bbf7d0' }]}>
            <Text style={[styles.counterNumber, { color: '#16a34a' }]}>{absen?.hadir ?? 0}</Text>
            <Text style={styles.counterLabel}>Hadir</Text>
          </View>
          <View style={[styles.counterBox, { backgroundColor: '#eff6ff', borderColor: '#bfdbfe' }]}>
            <Text style={[styles.counterNumber, { color: '#2563eb' }]}>{absen?.sakit ?? 0}</Text>
            <Text style={styles.counterLabel}>Sakit</Text>
          </View>
          <View style={[styles.counterBox, { backgroundColor: '#fffbeb', borderColor: '#fde68a' }]}>
            <Text style={[styles.counterNumber, { color: '#d97706' }]}>{absen?.izin ?? 0}</Text>
            <Text style={styles.counterLabel}>Izin</Text>
          </View>
          <View style={[styles.counterBox, { backgroundColor: '#fef2f2', borderColor: '#fecaca' }]}>
            <Text style={[styles.counterNumber, { color: '#dc2626' }]}>{absen?.alpha ?? 0}</Text>
            <Text style={styles.counterLabel}>Alpa</Text>
          </View>
        </View>

        {/* Progress Bar Kehadiran */}
        <View style={styles.progressContainer}>
          <View style={styles.progressHeader}>
            <Text style={styles.progressLabel}>Persentase Kehadiran</Text>
            <Text style={styles.progressPercent}>{absen?.persen_hadir ?? 100}%</Text>
          </View>
          <View style={styles.progressBarBg}>
            <View
              style={[
                styles.progressBarFill,
                { width: `${Math.min(100, absen?.persen_hadir ?? 100)}%` },
              ]}
            />
          </View>
        </View>
      </View>

      {/* 5 Penilaian Akademik Terbaru */}
      <View style={styles.sectionCard}>
        <View style={styles.sectionHeader}>
          <View>
            <Text style={styles.sectionTitle}>Nilai Terbaru</Text>
            <Text style={styles.sectionSubtitle}>Hasil penilaian tugas & ulangan</Text>
          </View>
          <TouchableOpacity
            style={styles.seeMoreBtn}
            onPress={() => navigation.navigate('RekapNilaiTab')}
          >
            <Text style={styles.seeMoreText}>Lihat Semua</Text>
            <Ionicons name="chevron-forward" size={14} color="#0284c7" />
          </TouchableOpacity>
        </View>

        {nilaiTerbaru.length === 0 ? (
          <View style={styles.emptyBox}>
            <Ionicons name="document-text-outline" size={36} color="#cbd5e1" />
            <Text style={styles.emptyText}>Belum ada nilai yang diinput guru.</Text>
          </View>
        ) : (
          nilaiTerbaru.map((item, idx) => (
            <View key={idx} style={styles.gradeItem}>
              <View style={styles.gradeIconContainer}>
                <Ionicons name="book-outline" size={20} color="#0284c7" />
              </View>
              <View style={styles.gradeInfo}>
                <Text style={styles.gradeMapel}>{item.mapel}</Text>
                <Text style={styles.gradeKegiatan} numberOfLines={1}>
                  {item.nama_kegiatan} • {item.jenis}
                </Text>
                <Text style={styles.gradeDate}>{item.tanggal_formatted}</Text>
              </View>
              <View style={styles.gradeScoreBox}>
                <Text style={styles.gradeScore}>{item.nilai}</Text>
                <View
                  style={[
                    styles.tuntasBadge,
                    { backgroundColor: item.is_tuntas ? '#dcfce7' : '#fee2e2' },
                  ]}
                >
                  <Text
                    style={[
                      styles.tuntasText,
                      { color: item.is_tuntas ? '#15803d' : '#b91c1c' },
                    ]}
                  >
                    {item.is_tuntas ? 'Tuntas' : 'Remedial'}
                  </Text>
                </View>
              </View>
            </View>
          ))
        )}
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
    padding: 16,
    paddingBottom: 28,
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f1f5f9',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 13,
    color: '#64748b',
    fontWeight: '500',
  },
  profileHeroCard: {
    backgroundColor: '#0284c7',
    borderRadius: 22,
    padding: 20,
    marginBottom: 16,
    overflow: 'hidden',
    position: 'relative',
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.22,
    shadowRadius: 12,
    elevation: 5,
  },
  heroCircle1: {
    position: 'absolute',
    top: -30,
    right: -20,
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: 'rgba(255, 255, 255, 0.12)',
  },
  heroCircle2: {
    position: 'absolute',
    bottom: -30,
    left: 20,
    width: 90,
    height: 90,
    borderRadius: 45,
    backgroundColor: 'rgba(255, 255, 255, 0.08)',
  },
  profileRow: {
    flexDirection: 'row',
    alignItems: 'center',
    zIndex: 1,
  },
  avatarCircle: {
    width: 62,
    height: 62,
    borderRadius: 31,
    backgroundColor: '#ffffff',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2.5,
    borderColor: 'rgba(255, 255, 255, 0.85)',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.15,
    shadowRadius: 5,
    elevation: 3,
  },
  avatarInitials: {
    fontSize: 26,
    fontWeight: '800',
    color: '#0284c7',
  },
  profileInfo: {
    flex: 1,
    marginLeft: 14,
  },
  greetingText: {
    fontSize: 12,
    color: '#e0f2fe',
    fontWeight: '500',
  },
  studentName: {
    fontSize: 17,
    fontWeight: '800',
    color: '#ffffff',
    marginTop: 1,
    letterSpacing: 0.3,
  },
  badgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 6,
    gap: 8,
  },
  classBadge: {
    backgroundColor: 'rgba(255, 255, 255, 0.22)',
    paddingHorizontal: 9,
    paddingVertical: 3,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.35)',
  },
  classBadgeText: {
    color: '#ffffff',
    fontSize: 11,
    fontWeight: '700',
  },
  nisText: {
    color: '#e0f2fe',
    fontSize: 11.5,
    fontWeight: '600',
  },
  waliCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.18)',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.28)',
    paddingHorizontal: 14,
    paddingVertical: 9,
    borderRadius: 12,
    marginTop: 14,
    gap: 8,
    zIndex: 1,
  },
  waliText: {
    fontSize: 12,
    color: '#e0f2fe',
    fontWeight: '500',
  },
  waliName: {
    fontWeight: '700',
    color: '#ffffff',
  },
  waliName: {
    fontWeight: '700',
    color: '#0c4a6e',
  },
  sectionCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.04,
    shadowRadius: 6,
    elevation: 2,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 14,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: '#0f172a',
  },
  sectionSubtitle: {
    fontSize: 11,
    color: '#64748b',
    marginTop: 1,
  },
  seeMoreBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 2,
  },
  seeMoreText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#0284c7',
  },
  counterGrid: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 14,
  },
  counterBox: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 12,
    alignItems: 'center',
    borderWidth: 1,
  },
  counterNumber: {
    fontSize: 18,
    fontWeight: '800',
  },
  counterLabel: {
    fontSize: 11,
    color: '#64748b',
    marginTop: 2,
    fontWeight: '500',
  },
  progressContainer: {
    backgroundColor: '#f8fafc',
    padding: 12,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#f1f5f9',
  },
  progressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
  progressLabel: {
    fontSize: 11.5,
    color: '#475569',
    fontWeight: '500',
  },
  progressPercent: {
    fontSize: 12,
    fontWeight: '700',
    color: '#16a34a',
  },
  progressBarBg: {
    height: 6,
    backgroundColor: '#e2e8f0',
    borderRadius: 3,
    overflow: 'hidden',
  },
  progressBarFill: {
    height: '100%',
    backgroundColor: '#16a34a',
    borderRadius: 3,
  },
  emptyBox: {
    alignItems: 'center',
    paddingVertical: 24,
  },
  emptyText: {
    fontSize: 12,
    color: '#94a3b8',
    marginTop: 8,
  },
  gradeItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  gradeIconContainer: {
    width: 38,
    height: 38,
    borderRadius: 10,
    backgroundColor: '#f0f9ff',
    justifyContent: 'center',
    alignItems: 'center',
  },
  gradeInfo: {
    flex: 1,
    marginLeft: 12,
  },
  gradeMapel: {
    fontSize: 13,
    fontWeight: '700',
    color: '#1e293b',
  },
  gradeKegiatan: {
    fontSize: 11,
    color: '#64748b',
    marginTop: 1,
  },
  gradeDate: {
    fontSize: 10,
    color: '#94a3b8',
    marginTop: 2,
  },
  gradeScoreBox: {
    alignItems: 'flex-end',
  },
  gradeScore: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0f172a',
  },
  tuntasBadge: {
    paddingHorizontal: 6,
    paddingVertical: 1.5,
    borderRadius: 4,
    marginTop: 2,
  },
  tuntasText: {
    fontSize: 9.5,
    fontWeight: '700',
  },
});
