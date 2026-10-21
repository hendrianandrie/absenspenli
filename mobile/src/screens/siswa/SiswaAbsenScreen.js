import React, { useState, useEffect } from 'react';
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
import apiClient from '../../config/api';

const MONTH_NAMES = [
  '',
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

export default function SiswaAbsenScreen() {
  const now = new Date();
  const [selectedMonth, setSelectedMonth] = useState(now.getMonth() + 1);
  const [selectedYear, setSelectedYear] = useState(now.getFullYear());

  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [absenData, setAbsenData] = useState(null);

  const fetchAbsensi = async (bulan, tahun) => {
    try {
      setLoading(true);
      const response = await apiClient.get(`/siswa/rekap-absen?bulan=${bulan}&tahun=${tahun}`);
      if (response.data && response.data.status === 'success') {
        setAbsenData(response.data.data);
      }
    } catch (e) {
      console.warn('Gagal memuat rekap absensi:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchAbsensi(selectedMonth, selectedYear);
  }, [selectedMonth, selectedYear]);

  const onRefresh = () => {
    setRefreshing(true);
    fetchAbsensi(selectedMonth, selectedYear);
  };

  const handlePrevMonth = () => {
    if (selectedMonth === 1) {
      setSelectedMonth(12);
      setSelectedYear((prev) => prev - 1);
    } else {
      setSelectedMonth((prev) => prev - 1);
    }
  };

  const handleNextMonth = () => {
    if (selectedMonth === 12) {
      setSelectedMonth(1);
      setSelectedYear((prev) => prev + 1);
    } else {
      setSelectedMonth((prev) => prev + 1);
    }
  };

  const stats = absenData?.statistik;
  const riwayat = absenData?.riwayat || [];

  const getStatusColor = (status) => {
    switch (status) {
      case 'Hadir':
        return { bg: '#dcfce7', text: '#15803d', icon: 'checkmark-circle' };
      case 'Sakit':
        return { bg: '#dbeafe', text: '#1d4ed8', icon: 'medical' };
      case 'Izin':
        return { bg: '#fef3c7', text: '#b45309', icon: 'document-text' };
      case 'Alpha':
        return { bg: '#fee2e2', text: '#b91c1c', icon: 'close-circle' };
      default:
        return { bg: '#f1f5f9', text: '#64748b', icon: 'help-circle' };
    }
  };

  return (
    <View style={styles.container}>
      {/* Month Selector Bar */}
      <View style={styles.monthSelectorBar}>
        <TouchableOpacity style={styles.arrowButton} onPress={handlePrevMonth}>
          <Ionicons name="chevron-back" size={20} color="#0f172a" />
        </TouchableOpacity>
        <View style={styles.monthDisplay}>
          <Ionicons name="calendar-outline" size={16} color="#0284c7" style={{ marginRight: 6 }} />
          <Text style={styles.monthYearText}>
            {MONTH_NAMES[selectedMonth]} {selectedYear}
          </Text>
        </View>
        <TouchableOpacity style={styles.arrowButton} onPress={handleNextMonth}>
          <Ionicons name="chevron-forward" size={20} color="#0f172a" />
        </TouchableOpacity>
      </View>

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#0284c7']} />}
      >
        {/* Ringkasan Statistik Kartu */}
        <View style={styles.statsCard}>
          <Text style={styles.statsHeaderTitle}>Rekapitulasi Kehadiran</Text>
          <View style={styles.statsGrid}>
            <View style={[styles.statBox, { borderLeftColor: '#16a34a' }]}>
              <Text style={styles.statLabel}>Hadir</Text>
              <Text style={[styles.statValue, { color: '#16a34a' }]}>{stats?.hadir ?? 0}</Text>
            </View>
            <View style={[styles.statBox, { borderLeftColor: '#2563eb' }]}>
              <Text style={styles.statLabel}>Sakit</Text>
              <Text style={[styles.statValue, { color: '#2563eb' }]}>{stats?.sakit ?? 0}</Text>
            </View>
            <View style={[styles.statBox, { borderLeftColor: '#d97706' }]}>
              <Text style={styles.statLabel}>Izin</Text>
              <Text style={[styles.statValue, { color: '#d97706' }]}>{stats?.izin ?? 0}</Text>
            </View>
            <View style={[styles.statBox, { borderLeftColor: '#dc2626' }]}>
              <Text style={styles.statLabel}>Alpa</Text>
              <Text style={[styles.statValue, { color: '#dc2626' }]}>{stats?.alpha ?? 0}</Text>
            </View>
          </View>
        </View>

        {/* Riwayat Harian Section */}
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Riwayat Kehadiran Harian</Text>
          <Text style={styles.sectionBadge}>{riwayat.length} Hari Tercatat</Text>
        </View>

        {loading ? (
          <View style={styles.loadingBox}>
            <ActivityIndicator size="small" color="#0284c7" />
            <Text style={styles.loadingSubtext}>Memuat riwayat...</Text>
          </View>
        ) : riwayat.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Ionicons name="calendar-clear-outline" size={48} color="#cbd5e1" />
            <Text style={styles.emptyTitle}>Belum Ada Data</Text>
            <Text style={styles.emptySubtitle}>
              Tidak ditemukan catatan kehadiran pada bulan {MONTH_NAMES[selectedMonth]} {selectedYear}.
            </Text>
          </View>
        ) : (
          riwayat.map((item) => {
            const style = getStatusColor(item.status);
            return (
              <View key={item.id} style={styles.logCard}>
                <View style={[styles.statusIconBox, { backgroundColor: style.bg }]}>
                  <Ionicons name={style.icon} size={22} color={style.text} />
                </View>
                <View style={styles.logInfo}>
                  <Text style={styles.logDate}>{item.tanggal_formatted}</Text>
                  <Text style={styles.logMeta}>
                    {item.waktu ? `Pukul ${item.waktu}` : 'Waktu tidak tercatat'} • {item.metode}
                  </Text>
                  {item.keterangan && item.keterangan !== '-' && (
                    <Text style={styles.logNotes}>Ket: {item.keterangan}</Text>
                  )}
                </View>
                <View style={[styles.statusPill, { backgroundColor: style.bg }]}>
                  <Text style={[styles.statusPillText, { color: style.text }]}>{item.status}</Text>
                </View>
              </View>
            );
          })
        )}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  monthSelectorBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: '#ffffff',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#e2e8f0',
  },
  arrowButton: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: '#f1f5f9',
    justifyContent: 'center',
    alignItems: 'center',
  },
  monthDisplay: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  monthYearText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#0f172a',
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 24,
  },
  statsCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  statsHeaderTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#334155',
    marginBottom: 12,
  },
  statsGrid: {
    flexDirection: 'row',
    gap: 8,
  },
  statBox: {
    flex: 1,
    backgroundColor: '#f8fafc',
    padding: 10,
    borderRadius: 10,
    borderLeftWidth: 3,
  },
  statLabel: {
    fontSize: 11,
    color: '#64748b',
    fontWeight: '500',
  },
  statValue: {
    fontSize: 18,
    fontWeight: '800',
    marginTop: 2,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: '#1e293b',
  },
  sectionBadge: {
    fontSize: 11,
    color: '#64748b',
    fontWeight: '600',
  },
  loadingBox: {
    paddingVertical: 40,
    alignItems: 'center',
  },
  loadingSubtext: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 8,
  },
  emptyContainer: {
    alignItems: 'center',
    paddingVertical: 48,
  },
  emptyTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: '#475569',
    marginTop: 12,
  },
  emptySubtitle: {
    fontSize: 12,
    color: '#94a3b8',
    textAlign: 'center',
    marginTop: 4,
    paddingHorizontal: 32,
    lineHeight: 18,
  },
  logCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ffffff',
    padding: 14,
    borderRadius: 14,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  statusIconBox: {
    width: 42,
    height: 42,
    borderRadius: 21,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  logInfo: {
    flex: 1,
  },
  logDate: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0f172a',
  },
  logMeta: {
    fontSize: 11,
    color: '#64748b',
    marginTop: 2,
  },
  logNotes: {
    fontSize: 11,
    color: '#d97706',
    marginTop: 2,
    fontStyle: 'italic',
  },
  statusPill: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusPillText: {
    fontSize: 11,
    fontWeight: '700',
  },
});
