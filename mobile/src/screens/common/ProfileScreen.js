import React, { useContext, useState } from 'react';
import { StyleSheet, Text, View, TouchableOpacity, Alert, ScrollView, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as Notifications from 'expo-notifications';
import { AuthContext } from '../../context/AuthContext';
import apiClient, { DEFAULT_BASE_URL } from '../../config/api';
import { registerForPushNotificationsAsync } from '../../services/notificationService';

export default function ProfileScreen() {
  const { user, logout } = useContext(AuthContext);
  const [testingNotif, setTestingNotif] = useState(false);

  const handleLogout = () => {
    Alert.alert(
      'Konfirmasi Keluar',
      'Apakah Anda yakin ingin keluar dari akun ini?',
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

  const handleResetAndLogout = () => {
    Alert.alert(
      'Reset Sesi & Server',
      'Kembalikan alamat server ke default (smpn5ciamis.sch.id) dan keluar ke halaman login?',
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Reset & Keluar',
          style: 'destructive',
          onPress: async () => {
            await AsyncStorage.removeItem('custom_base_url');
            await logout();
          },
        },
      ]
    );
  };

  const handleTestNotification = async () => {
    setTestingNotif(true);
    try {
      // 1. Daftarkan / pastikan token HP ini tersimpan di database server
      const regRes = await registerForPushNotificationsAsync();
      if (!regRes?.success) {
        Alert.alert('Info Pendaftaran', regRes?.message || 'Gagal mendaftarkan token notifikasi HP.');
        setTestingNotif(false);
        return;
      }

      // 2. Tampilkan notifikasi pop-up langsung di layar HP (banner + bunyi)
      try {
        await Notifications.scheduleNotificationAsync({
          content: {
            title: '🔔 Notifikasi SI-KASEP Aktif!',
            body: `Halo ${user?.name || 'Siswa'}, perangkat ini berhasil terhubung untuk notifikasi presensi & nilai.`,
            data: { type: 'test' },
            sound: 'default',
          },
          trigger: null,
        });
      } catch (localErr) {
        console.warn('Local notification error:', localErr);
      }

      // 3. Panggil API server untuk mengirimkan notifikasi dari backend
      const res = await apiClient.post('/device-token/test');
      if (res.data?.status === 'success') {
        Alert.alert('Berhasil 🔔', 'Perangkat berhasil didaftarkan dan notifikasi tes telah dikirim! Periksa banner notifikasi di HP Anda.');
      } else {
        Alert.alert('Perangkat Terhubung 🔔', res.data?.message || 'Token perangkat telah terdaftar di database server.');
      }
    } catch (e) {
      Alert.alert('Info', e.response?.data?.message || e.message || 'Koneksi ke server bermasalah.');
    } finally {
      setTestingNotif(false);
    }
  };

  const getRoleLabel = (role) => {
    switch (role) {
      case 'siswa':
        return 'Siswa';
      case 'guru':
        return 'Guru Mata Pelajaran';
      case 'walikelas':
        return 'Wali Kelas';
      case 'piket':
        return 'Petugas Piket';
      case 'admin':
        return 'Administrator';
      default:
        return role;
    }
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      {/* Profile Card */}
      <View style={styles.profileCard}>
        <View style={styles.avatar}>
          <Text style={styles.avatarText}>
            {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
          </Text>
        </View>
        <Text style={styles.userName}>{user?.name}</Text>
        <View style={styles.roleBadge}>
          <Text style={styles.roleText}>{getRoleLabel(user?.role)}</Text>
        </View>
      </View>

      {/* Info Details Card */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Informasi Akun</Text>

        <View style={styles.infoRow}>
          <Ionicons name="finger-print-outline" size={18} color="#64748b" style={styles.infoIcon} />
          <View style={styles.infoContent}>
            <Text style={styles.infoLabel}>Username / NIS</Text>
            <Text style={styles.infoValue}>{user?.username || '-'}</Text>
          </View>
        </View>

        {user?.siswa?.kelas && (
          <View style={styles.infoRow}>
            <Ionicons name="school-outline" size={18} color="#64748b" style={styles.infoIcon} />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Kelas</Text>
              <Text style={styles.infoValue}>Kelas {user?.siswa?.kelas}</Text>
            </View>
          </View>
        )}

        {user?.is_wali_kelas && (
          <View style={styles.infoRow}>
            <Ionicons name="people-outline" size={18} color="#64748b" style={styles.infoIcon} />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Kelas Binaan</Text>
              <Text style={styles.infoValue}>Kelas {user?.kelas_binaan}</Text>
            </View>
          </View>
        )}

        <View style={styles.infoRow}>
          <Ionicons name="mail-outline" size={18} color="#64748b" style={styles.infoIcon} />
          <View style={styles.infoContent}>
            <Text style={styles.infoLabel}>Email</Text>
            <Text style={styles.infoValue}>{user?.email || '-'}</Text>
          </View>
        </View>

        <View style={[styles.infoRow, { borderBottomWidth: 0 }]}>
          <Ionicons name="server-outline" size={18} color="#64748b" style={styles.infoIcon} />
          <View style={styles.infoContent}>
            <Text style={styles.infoLabel}>Default Server</Text>
            <Text style={styles.infoValue} numberOfLines={1}>
              {DEFAULT_BASE_URL}
            </Text>
          </View>
        </View>
      </View>

      {/* Tentang Aplikasi */}
      <View style={styles.card}>
        <Text style={styles.cardTitle}>Tentang Aplikasi</Text>
        <Text style={styles.aboutText}>
          <Text style={{ fontWeight: '700' }}>SI-KASEP</Text> (Sistem Informasi Kehadiran & Akademik Siswa) adalah platform resmi SMP Negeri 5 Ciamis untuk mendukung pencatatan kehadiran presisi dan transparansi nilai peserta didik.
        </Text>
        <Text style={styles.versionText}>Versi 1.0.0 (React Native Mobile)</Text>
      </View>

      {/* Test Push Notification Button */}
      <TouchableOpacity 
        style={styles.testBtn} 
        onPress={handleTestNotification} 
        disabled={testingNotif}
        activeOpacity={0.8}
      >
        {testingNotif ? (
          <ActivityIndicator size="small" color="#059669" />
        ) : (
          <Ionicons name="notifications-outline" size={20} color="#059669" />
        )}
        <Text style={styles.testBtnText}>
          {testingNotif ? 'Menghubungkan ke Server...' : 'Tes Push Notifikasi ke HP Ini'}
        </Text>
      </TouchableOpacity>

      {/* Reset Server & Logout Button */}
      <TouchableOpacity style={styles.resetBtn} onPress={handleResetAndLogout} activeOpacity={0.8}>
        <Ionicons name="refresh-circle-outline" size={20} color="#0284c7" />
        <Text style={styles.resetBtnText}>Reset Server ke Default & Keluar</Text>
      </TouchableOpacity>

      {/* Logout Button */}
      <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout} activeOpacity={0.8}>
        <Ionicons name="log-out-outline" size={20} color="#dc2626" />
        <Text style={styles.logoutBtnText}>Keluar dari Akun</Text>
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
  profileCard: {
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 20,
    padding: 24,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  avatar: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: '#0284c7',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  avatarText: {
    fontSize: 28,
    fontWeight: '800',
    color: '#ffffff',
  },
  userName: {
    fontSize: 18,
    fontWeight: '800',
    color: '#0f172a',
    textAlign: 'center',
  },
  roleBadge: {
    backgroundColor: '#e0f2fe',
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 12,
    marginTop: 6,
  },
  roleText: {
    fontSize: 11.5,
    fontWeight: '700',
    color: '#0369a1',
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
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  infoIcon: {
    marginRight: 12,
  },
  infoContent: {
    flex: 1,
  },
  infoLabel: {
    fontSize: 11,
    color: '#64748b',
  },
  infoValue: {
    fontSize: 13,
    fontWeight: '600',
    color: '#0f172a',
    marginTop: 1,
  },
  aboutText: {
    fontSize: 12,
    color: '#475569',
    lineHeight: 18,
  },
  versionText: {
    fontSize: 11,
    color: '#94a3b8',
    marginTop: 8,
    fontWeight: '500',
  },
  testBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: '#ecfdf5',
    borderWidth: 1,
    borderColor: '#a7f3d0',
    paddingVertical: 14,
    borderRadius: 14,
    marginBottom: 10,
  },
  testBtnText: {
    color: '#059669',
    fontSize: 14,
    fontWeight: '700',
  },
  resetBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#e0f2fe',
    borderWidth: 1,
    borderColor: '#bae6fd',
    paddingVertical: 14,
    borderRadius: 12,
    marginTop: 8,
    marginBottom: 8,
    gap: 8,
  },
  resetBtnText: {
    fontSize: 14,
    fontWeight: '700',
    color: '#0284c7',
  },
  logoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fee2e2',
    borderWidth: 1,
    borderColor: '#fecaca',
    paddingVertical: 14,
    borderRadius: 12,
    marginTop: 4,
    gap: 8,
  },
  logoutBtnText: {
    fontSize: 14,
    fontWeight: '700',
    color: '#dc2626',
  },
});
