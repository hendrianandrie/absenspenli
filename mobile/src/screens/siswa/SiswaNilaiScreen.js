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

export default function SiswaNilaiScreen() {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState(null);
  const [dataNilai, setDataNilai] = useState(null);
  const [selectedMapelId, setSelectedMapelId] = useState(null); // null = Semua Mapel

  const fetchNilai = async () => {
    try {
      setLoading(true);
      setErrorMessage(null);
      const response = await apiClient.get('/siswa/rekap-nilai');
      if (response.data && response.data.status === 'success') {
        setDataNilai(response.data.data);
      } else {
        setErrorMessage(response.data?.message || 'Gagal memuat rekap nilai.');
      }
    } catch (e) {
      console.warn('Gagal memuat rekap nilai:', e);
      const attemptedUrl = (e.config?.baseURL || '') + (e.config?.url || '');
      if (e.response?.status === 401) {
        setErrorMessage('Sesi login telah kedaluwarsa. Silakan Logout dari tab Profil lalu Login kembali.');
      } else if (e.response?.status === 404) {
        setErrorMessage(`Endpoint tidak ditemukan (404) pada:\n${attemptedUrl}`);
      } else {
        setErrorMessage(e.response?.data?.message || e.message || 'Gagal memuat rekap nilai.');
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchNilai();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchNilai();
  };

  const mapelList = dataNilai?.mapel_list || [];
  const rekapList = dataNilai?.rekap_nilai || [];

  // Filter rekap berdasarkan mapel yang dipilih
  const filteredRekap = selectedMapelId
    ? rekapList.filter((item) => item.mapel_id === selectedMapelId)
    : rekapList;

  return (
    <View style={styles.container}>
      {/* Horizontal Mapel Selector Chips */}
      <View style={styles.chipBar}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.chipScroll}>
          <TouchableOpacity
            style={[styles.chip, selectedMapelId === null && styles.chipActive]}
            onPress={() => setSelectedMapelId(null)}
          >
            <Text style={[styles.chipText, selectedMapelId === null && styles.chipTextActive]}>
              Semua Mapel
            </Text>
          </TouchableOpacity>
          {mapelList.map((m) => (
            <TouchableOpacity
              key={m.id}
              style={[styles.chip, selectedMapelId === m.id && styles.chipActive]}
              onPress={() => setSelectedMapelId(m.id)}
            >
              <Text style={[styles.chipText, selectedMapelId === m.id && styles.chipTextActive]}>
                {m.nama}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#0284c7']} />}
      >
        {loading ? (
          <View style={styles.loadingBox}>
            <ActivityIndicator size="small" color="#0284c7" />
            <Text style={styles.loadingSubtext}>Memuat rekap nilai...</Text>
          </View>
        ) : errorMessage ? (
          <View style={styles.emptyContainer}>
            <Ionicons name="alert-circle-outline" size={48} color="#ef4444" />
            <Text style={[styles.emptyTitle, { color: '#dc2626' }]}>Gagal Memuat Nilai</Text>
            <Text style={styles.emptySubtitle}>{errorMessage}</Text>
            <TouchableOpacity style={styles.retryBtn} onPress={fetchNilai}>
              <Ionicons name="refresh" size={16} color="#ffffff" style={{ marginRight: 6 }} />
              <Text style={styles.retryBtnText}>Muat Ulang</Text>
            </TouchableOpacity>
          </View>
        ) : filteredRekap.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Ionicons name="folder-open-outline" size={48} color="#cbd5e1" />
            <Text style={styles.emptyTitle}>Belum Ada Data Penilaian</Text>
            <Text style={styles.emptySubtitle}>Mata pelajaran ini belum memiliki kegiatan penilaian.</Text>
          </View>
        ) : (
          filteredRekap.map((mapelItem) => {
            const stat = mapelItem.statistik;
            const isTuntas = stat.status_akhir === 'Tuntas';

            return (
              <View key={mapelItem.mapel_id} style={styles.mapelCard}>
                {/* Header Mata Pelajaran */}
                <View style={styles.mapelHeader}>
                  <View style={styles.mapelTitleBox}>
                    <Text style={styles.mapelTitle}>{mapelItem.nama_mapel}</Text>
                    <View style={styles.kkmBadge}>
                      <Text style={styles.kkmText}>KKM: {mapelItem.kkm}</Text>
                    </View>
                  </View>
                  <View style={styles.avgBox}>
                    <Text style={styles.avgLabel}>Rata-Rata</Text>
                    <Text style={styles.avgValue}>{stat.rata_rata ?? '-'}</Text>
                  </View>
                </View>

                {/* Sub-Statistik Bar */}
                <View style={styles.statRow}>
                  <Text style={styles.statSubText}>
                    Total Tugas: <Text style={styles.boldText}>{stat.total_kegiatan}</Text>
                  </Text>
                  <Text style={styles.statSubText}>
                    Tuntas: <Text style={[styles.boldText, { color: '#16a34a' }]}>{stat.total_tuntas}</Text>
                  </Text>
                  <Text style={styles.statSubText}>
                    Remedial: <Text style={[styles.boldText, { color: '#dc2626' }]}>{stat.total_remedial}</Text>
                  </Text>
                </View>

                {/* List Penilaian */}
                {mapelItem.penilaian.length === 0 ? (
                  <View style={styles.noAssessBox}>
                    <Text style={styles.noAssessText}>Belum ada rincian tugas untuk mapel ini.</Text>
                  </View>
                ) : (
                  <View style={styles.assessmentList}>
                    {mapelItem.penilaian.map((p, idx) => (
                      <View key={idx} style={styles.assessmentItem}>
                        <View style={styles.assessLeft}>
                          <Text style={styles.assessTitle}>{p.nama_kegiatan}</Text>
                          <Text style={styles.assessMeta}>
                            {p.jenis} • {p.tanggal_formatted}
                          </Text>
                        </View>
                        <View style={styles.assessRight}>
                          <Text style={styles.assessScore}>
                            {p.nilai !== null ? p.nilai : '-'}
                          </Text>
                          {p.nilai !== null && (
                            <View
                              style={[
                                styles.pillStatus,
                                { backgroundColor: p.is_tuntas ? '#dcfce7' : '#fee2e2' },
                              ]}
                            >
                              <Text
                                style={[
                                  styles.pillStatusText,
                                  { color: p.is_tuntas ? '#15803d' : '#b91c1c' },
                                ]}
                              >
                                {p.is_tuntas ? 'Tuntas' : 'Remed'}
                              </Text>
                            </View>
                          )}
                        </View>
                      </View>
                    ))}
                  </View>
                )}
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
  chipBar: {
    backgroundColor: '#ffffff',
    borderBottomWidth: 1,
    borderBottomColor: '#e2e8f0',
    paddingVertical: 10,
  },
  chipScroll: {
    paddingHorizontal: 16,
    gap: 8,
  },
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 20,
    backgroundColor: '#f1f5f9',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  chipActive: {
    backgroundColor: '#0284c7',
    borderColor: '#0284c7',
  },
  chipText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#475569',
  },
  chipTextActive: {
    color: '#ffffff',
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 28,
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
  },
  mapelCard: {
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
  mapelHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
    paddingBottom: 12,
  },
  mapelTitleBox: {
    flex: 1,
    marginRight: 10,
  },
  mapelTitle: {
    fontSize: 15,
    fontWeight: '800',
    color: '#0f172a',
  },
  kkmBadge: {
    alignSelf: 'flex-start',
    backgroundColor: '#e0f2fe',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 6,
    marginTop: 4,
  },
  kkmText: {
    fontSize: 10.5,
    fontWeight: '700',
    color: '#0369a1',
  },
  avgBox: {
    alignItems: 'flex-end',
    backgroundColor: '#f8fafc',
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  avgLabel: {
    fontSize: 10,
    color: '#64748b',
    fontWeight: '500',
  },
  avgValue: {
    fontSize: 18,
    fontWeight: '800',
    color: '#0f172a',
  },
  statRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    backgroundColor: '#f8fafc',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
    marginTop: 10,
  },
  statSubText: {
    fontSize: 11,
    color: '#64748b',
  },
  boldText: {
    fontWeight: '700',
    color: '#334155',
  },
  noAssessBox: {
    paddingVertical: 14,
    alignItems: 'center',
  },
  noAssessText: {
    fontSize: 11.5,
    color: '#94a3b8',
    fontStyle: 'italic',
  },
  assessmentList: {
    marginTop: 10,
  },
  assessmentItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f8fafc',
  },
  assessLeft: {
    flex: 1,
    marginRight: 12,
  },
  assessTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#1e293b',
  },
  assessMeta: {
    fontSize: 11,
    color: '#94a3b8',
    marginTop: 2,
  },
  assessRight: {
    alignItems: 'flex-end',
  },
  assessScore: {
    fontSize: 15,
    fontWeight: '800',
    color: '#0f172a',
  },
  pillStatus: {
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 4,
    marginTop: 2,
  },
  pillStatusText: {
    fontSize: 9.5,
    fontWeight: '700',
  },
  retryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#0284c7',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 10,
    marginTop: 14,
  },
  retryBtnText: {
    color: '#ffffff',
    fontSize: 13,
    fontWeight: '700',
  },
});
