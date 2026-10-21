import React, { useContext } from 'react';
import { StyleSheet, Text, View, TouchableOpacity, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { AuthContext } from '../../context/AuthContext';

export default function StaffDashboardScreen({ navigation }) {
  const { user, logout } = useContext(AuthContext);

  const isWali = user?.is_wali_kelas;

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      {/* Header Card */}
      <View style={styles.headerCard}>
        <View style={styles.avatarCircle}>
          <Ionicons name="person" size={32} color="#0284c7" />
        </View>
        <Text style={styles.nameText}>{user?.name}</Text>
        <Text style={styles.roleText}>
          {user?.role === 'admin' ? 'Administrator' : (isWali ? `Guru & Wali Kelas ${user?.kelas_binaan || ''}` : 'Guru Mata Pelajaran')}
        </Text>
      </View>

      {/* Info Notice */}
      <View style={styles.noticeCard}>
        <Ionicons name="information-circle" size={24} color="#0284c7" />
        <View style={styles.noticeContent}>
          <Text style={styles.noticeTitle}>Portal Pendidik Mobile</Text>
          <Text style={styles.noticeDesc}>
            Anda telah berhasil masuk ke aplikasi SI-KASEP. Untuk penginputan nilai massal dan rekap rapor semester saat ini tetap dapat diakses dengan optimal melalui versi web browser.
          </Text>
        </View>
      </View>

      {/* Role Summary */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Data Penugasan</Text>
        <View style={styles.row}>
          <Text style={styles.label}>NIP / Username:</Text>
          <Text style={styles.val}>{user?.username || '-'}</Text>
        </View>
        {isWali && (
          <View style={styles.row}>
            <Text style={styles.label}>Kelas Binaan:</Text>
            <Text style={[styles.val, { color: '#0284c7', fontWeight: '700' }]}>Kelas {user?.kelas_binaan}</Text>
          </View>
        )}
        <View style={styles.row}>
          <Text style={styles.label}>Email:</Text>
          <Text style={styles.val}>{user?.email || '-'}</Text>
        </View>
      </View>

      {/* Tombol Logout */}
      <TouchableOpacity style={styles.logoutBtn} onPress={logout}>
        <Ionicons name="log-out-outline" size={18} color="#dc2626" />
        <Text style={styles.logoutText}>Keluar dari Aplikasi</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  content: {
    padding: 16,
    paddingBottom: 32,
  },
  headerCard: {
    backgroundColor: '#0284c7',
    borderRadius: 20,
    padding: 24,
    alignItems: 'center',
    marginBottom: 16,
  },
  avatarCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#e0f2fe',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  nameText: {
    fontSize: 18,
    fontWeight: '800',
    color: '#ffffff',
    textAlign: 'center',
  },
  roleText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#e0f2fe',
    marginTop: 4,
  },
  noticeCard: {
    flexDirection: 'row',
    backgroundColor: '#f0f9ff',
    borderWidth: 1,
    borderColor: '#bae6fd',
    borderRadius: 14,
    padding: 14,
    marginBottom: 16,
    alignItems: 'flex-start',
    gap: 12,
  },
  noticeContent: {
    flex: 1,
  },
  noticeTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0369a1',
  },
  noticeDesc: {
    fontSize: 11.5,
    color: '#0c4a6e',
    marginTop: 2,
    lineHeight: 17,
  },
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  cardTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: '#1e293b',
    marginBottom: 12,
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  label: {
    fontSize: 12,
    color: '#64748b',
  },
  val: {
    fontSize: 12,
    fontWeight: '600',
    color: '#0f172a',
  },
  logoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fee2e2',
    paddingVertical: 12,
    borderRadius: 12,
    gap: 6,
    marginTop: 8,
  },
  logoutText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#dc2626',
  },
});
