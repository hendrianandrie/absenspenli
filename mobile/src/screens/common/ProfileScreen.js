import React, { useContext } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TouchableOpacity,
  Alert,
  ScrollView,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { AuthContext } from '../../context/AuthContext';

export default function ProfileScreen() {
  const { user, logout } = useContext(AuthContext);

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
    <ScrollView
      style={styles.container}
      contentContainerStyle={styles.scrollContent}
      showsVerticalScrollIndicator={false}
      bounces={false}
    >
      {/* Top Banner Melengkung dengan Ornamen Lingkaran */}
      <View style={styles.topBanner}>
        {/* Ornamen Lingkaran Transparan */}
        <View style={styles.decorCircle1} />
        <View style={styles.decorCircle2} />
        <View style={styles.decorCircle3} />

        {/* Avatar Profil Timbul */}
        <View style={styles.avatarContainer}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>
              {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
            </Text>
          </View>
        </View>

        {/* Nama dan Status Pengguna */}
        <Text style={styles.userName} numberOfLines={2}>
          {user?.name || 'Pengguna'}
        </Text>
        <View style={styles.roleBadge}>
          <Text style={styles.roleText}>{getRoleLabel(user?.role)}</Text>
        </View>
      </View>

      {/* Floating Cards Container */}
      <View style={styles.cardContainer}>
        {/* Info Details Card */}
        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.cardHeaderIconBox}>
              <Ionicons name="id-card-outline" size={16} color="#0284c7" />
            </View>
            <Text style={styles.cardTitle}>Informasi Akun</Text>
          </View>

          {/* Row Username / NIS */}
          <View style={styles.infoRow}>
            <View style={styles.iconBox}>
              <Ionicons name="finger-print-outline" size={19} color="#0284c7" />
            </View>
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Username / NIS</Text>
              <Text style={styles.infoValue}>{user?.username || '-'}</Text>
            </View>
          </View>

          {/* Row Kelas (Jika Akun Siswa) */}
          {user?.siswa?.kelas && (
            <View style={styles.infoRow}>
              <View style={styles.iconBox}>
                <Ionicons name="school-outline" size={19} color="#0284c7" />
              </View>
              <View style={styles.infoContent}>
                <Text style={styles.infoLabel}>Kelas</Text>
                <Text style={styles.infoValue}>Kelas {user?.siswa?.kelas}</Text>
              </View>
            </View>
          )}

          {/* Row Kelas Binaan (Jika Wali Kelas) */}
          {user?.is_wali_kelas && (
            <View style={styles.infoRow}>
              <View style={styles.iconBox}>
                <Ionicons name="people-outline" size={19} color="#0284c7" />
              </View>
              <View style={styles.infoContent}>
                <Text style={styles.infoLabel}>Kelas Binaan</Text>
                <Text style={styles.infoValue}>Kelas {user?.kelas_binaan}</Text>
              </View>
            </View>
          )}

          {/* Row Email */}
          <View style={[styles.infoRow, { borderBottomWidth: 0, paddingBottom: 4 }]}>
            <View style={styles.iconBox}>
              <Ionicons name="mail-outline" size={19} color="#0284c7" />
            </View>
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Email</Text>
              <Text style={styles.infoValue}>{user?.email || '-'}</Text>
            </View>
          </View>
        </View>

        {/* Tentang Aplikasi */}
        <View style={styles.card}>
          <View style={styles.aboutHeader}>
            <Image
              source={require('../../../assets/logo.png')}
              style={styles.aboutLogo}
              resizeMode="contain"
            />
            <View style={{ flex: 1 }}>
              <Text style={styles.aboutBrandTitle}>SI-KASEP</Text>
              <Text style={styles.aboutBrandSubtitle}>SMP NEGERI 5 CIAMIS</Text>
            </View>
          </View>
          <Text style={styles.aboutText}>
            <Text style={{ fontWeight: '700', color: '#0f172a' }}>SI-KASEP</Text> (Sistem Informasi Rekap Absen dan Penilaian) adalah platform resmi SMP Negeri 5 Ciamis untuk mendukung pencatatan kehadiran presisi dan transparansi nilai peserta didik.
          </Text>
          <View style={styles.versionBadge}>
            <Ionicons name="information-circle-outline" size={14} color="#64748b" />
            <Text style={styles.versionText}>Versi 1.0.0 (React Native Mobile)</Text>
          </View>
        </View>

        {/* Logout Button */}
        <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout} activeOpacity={0.8}>
          <Ionicons name="log-out-outline" size={20} color="#dc2626" />
          <Text style={styles.logoutBtnText}>Keluar dari Akun</Text>
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
    paddingTop: 16,
    paddingBottom: 46,
    borderBottomLeftRadius: 36,
    borderBottomRightRadius: 36,
    alignItems: 'center',
    position: 'relative',
    overflow: 'hidden',
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.2,
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
    width: 78,
    height: 78,
    borderRadius: 39,
    backgroundColor: '#ffffff',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: '#e0f2fe',
  },
  avatarText: {
    fontSize: 32,
    fontWeight: '800',
    color: '#0284c7',
  },
  userName: {
    fontSize: 18.5,
    fontWeight: '800',
    color: '#ffffff',
    textAlign: 'center',
    paddingHorizontal: 24,
    letterSpacing: 0.3,
    lineHeight: 24,
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
    fontSize: 12,
    fontWeight: '700',
    color: '#ffffff',
    letterSpacing: 0.5,
  },
  cardContainer: {
    paddingHorizontal: 20,
    marginTop: -20,
    zIndex: 2,
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
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 11,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  iconBox: {
    width: 38,
    height: 38,
    borderRadius: 11,
    backgroundColor: '#f0f9ff',
    borderWidth: 1,
    borderColor: '#e0f2fe',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 14,
  },
  infoContent: {
    flex: 1,
    justifyContent: 'center',
  },
  infoLabel: {
    fontSize: 11,
    color: '#64748b',
    fontWeight: '600',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  infoValue: {
    fontSize: 14,
    fontWeight: '700',
    color: '#0f172a',
    marginTop: 2,
  },
  aboutHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 12,
  },
  aboutLogo: {
    width: 44,
    height: 44,
    borderRadius: 22,
    borderWidth: 1.5,
    borderColor: '#e0f2fe',
    backgroundColor: '#ffffff',
  },
  aboutBrandTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0f172a',
    letterSpacing: 0.5,
  },
  aboutBrandSubtitle: {
    fontSize: 11,
    fontWeight: '700',
    color: '#0284c7',
    letterSpacing: 0.5,
  },
  aboutText: {
    fontSize: 12,
    color: '#475569',
    lineHeight: 19,
  },
  versionBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: '#f8fafc',
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    marginTop: 12,
  },
  versionText: {
    fontSize: 11,
    color: '#64748b',
    fontWeight: '600',
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
    marginTop: 2,
    gap: 8,
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
