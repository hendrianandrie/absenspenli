import React, { useState, useContext } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  Image,
} from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { Ionicons } from '@expo/vector-icons';
import { AuthContext } from '../../context/AuthContext';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { DEFAULT_BASE_URL, normalizeBaseUrl } from '../../config/api';

export default function LoginScreen() {
  const { login } = useContext(AuthContext);

  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Pengaturan Server URL dinamis
  const [showServerSetting, setShowServerSetting] = useState(false);
  const [serverUrl, setServerUrl] = useState(DEFAULT_BASE_URL);
  const [devTapCount, setDevTapCount] = useState(0);

  React.useEffect(() => {
    AsyncStorage.getItem('custom_base_url').then((saved) => {
      // Bersihkan jika ada setting tunnel lama atau URL yang belum lengkap /api/v1
      if (saved && (saved.includes('loca.lt') || saved.includes('trycloudflare.com') || !saved.includes('/api/v1'))) {
        AsyncStorage.removeItem('custom_base_url');
        setServerUrl(DEFAULT_BASE_URL);
      } else if (saved) {
        setServerUrl(saved);
      } else {
        setServerUrl(DEFAULT_BASE_URL);
      }
    });
  }, []);

  const handleLogin = async () => {
    if (!username.trim() || !password.trim()) {
      Alert.alert('Perhatian', 'Silakan masukkan Username / NIS dan Password Anda.');
      return;
    }

    setIsSubmitting(true);
    const result = await login(username, password);
    setIsSubmitting(false);

    if (!result.success) {
      Alert.alert('Login Gagal', result.message);
    }
  };

  const handleSaveServerUrl = async () => {
    try {
      const normalized = normalizeBaseUrl(serverUrl);
      await AsyncStorage.setItem('custom_base_url', normalized);
      setServerUrl(normalized);
      Alert.alert('Tersimpan', `Alamat server berhasil disetel ke: ${normalized}`);
      setShowServerSetting(false);
    } catch (e) {
      Alert.alert('Error', 'Gagal menyimpan pengaturan server.');
    }
  };

  const handleResetServerUrl = async () => {
    try {
      await AsyncStorage.removeItem('custom_base_url');
      setServerUrl(DEFAULT_BASE_URL);
      Alert.alert('Reset Berhasil', `Alamat server dikembalikan ke default: ${DEFAULT_BASE_URL}`);
      setShowServerSetting(false);
    } catch (e) {
      Alert.alert('Error', 'Gagal mereset server.');
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      style={styles.container}
    >
      <StatusBar style="dark" />

      {/* Ambient Mesh Background Blobs (Pilihan 2: Soft Mesh) */}
      <View style={styles.meshBlobTopRight} pointerEvents="none" />
      <View style={styles.meshBlobTopLeft} pointerEvents="none" />
      <View style={styles.meshBlobBottomLeft} pointerEvents="none" />
      <View style={styles.meshBlobBottomRight} pointerEvents="none" />

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Header Logo & Branding */}
        <View style={styles.header}>
          <View style={styles.logoContainer}>
            <Image
              source={require('../../../assets/logo.png')}
              style={styles.logoImage}
              resizeMode="contain"
            />
          </View>
          <Text style={styles.brandTitle}>SI-KASEP</Text>
          <View style={styles.badgePill}>
            <Text style={styles.badgeText}>SMP NEGERI 5 CIAMIS</Text>
          </View>
          <Text style={styles.brandDescription}>
            Sistem Informasi Rekap Absen dan Penilaian
          </Text>
        </View>

        {/* Glassmorphism Card */}
        <View style={styles.card}>
          <Text style={styles.formTitle}>Masuk ke Akun</Text>
          <Text style={styles.formSubtitle}>
            Gunakan NIS untuk Siswa, atau NIP/Email untuk Guru & Tenaga Kependidikan.
          </Text>

          {/* Input Username / NIS */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>NIS / NIP / Username</Text>
            <View style={styles.inputContainer}>
              <Ionicons name="person-outline" size={20} color="#64748b" style={styles.inputIcon} />
              <TextInput
                style={styles.input}
                placeholder="Contoh: 0111582931"
                placeholderTextColor="#94a3b8"
                value={username}
                onChangeText={setUsername}
                autoCapitalize="none"
                autoCorrect={false}
              />
            </View>
          </View>

          {/* Input Password */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Kata Sandi</Text>
            <View style={styles.inputContainer}>
              <Ionicons name="lock-closed-outline" size={20} color="#64748b" style={styles.inputIcon} />
              <TextInput
                style={styles.input}
                placeholder="Masukkan kata sandi Anda"
                placeholderTextColor="#94a3b8"
                value={password}
                onChangeText={setPassword}
                secureTextEntry={!showPassword}
              />
              <TouchableOpacity
                onPress={() => setShowPassword(!showPassword)}
                style={styles.eyeButton}
              >
                <Ionicons
                  name={showPassword ? 'eye-off-outline' : 'eye-outline'}
                  size={20}
                  color="#64748b"
                />
              </TouchableOpacity>
            </View>
          </View>

          {/* Tombol Login */}
          <TouchableOpacity
            style={[styles.loginButton, isSubmitting && styles.buttonDisabled]}
            onPress={handleLogin}
            disabled={isSubmitting}
            activeOpacity={0.8}
          >
            {isSubmitting ? (
              <ActivityIndicator color="#ffffff" />
            ) : (
              <View style={styles.buttonInner}>
                <Text style={styles.buttonText}>Masuk Sekarang</Text>
                <Ionicons name="arrow-forward" size={18} color="#ffffff" style={{ marginLeft: 8 }} />
              </View>
            )}
          </TouchableOpacity>
        </View>

        {/* Footer (Ketuk 5x jika ingin membuka menu rahasia ubah server pengembang) */}
        <TouchableOpacity
          activeOpacity={1}
          onPress={() => {
            setDevTapCount((prev) => {
              if (prev + 1 >= 5) {
                setShowServerSetting(!showServerSetting);
                return 0;
              }
              return prev + 1;
            });
          }}
        >
          <Text style={styles.footerText}>
            Aplikasi Resmi SMPN 5 Ciamis • Versi 1.0 Mobile
          </Text>
        </TouchableOpacity>

        {showServerSetting && (
          <View style={styles.serverSettingBox}>
            <Text style={styles.serverLabel}>Alamat API Backend (Menu Pengembang):</Text>
            <TextInput
              style={styles.serverInput}
              value={serverUrl}
              onChangeText={setServerUrl}
              placeholder="https://smpn5ciamis.sch.id/api/v1"
              autoCapitalize="none"
            />
            <View style={{ flexDirection: 'row', gap: 8, marginTop: 8 }}>
              <TouchableOpacity style={[styles.saveServerBtn, { flex: 1 }]} onPress={handleSaveServerUrl}>
                <Text style={styles.saveServerBtnText}>Simpan Server</Text>
              </TouchableOpacity>
              <TouchableOpacity style={[styles.saveServerBtn, { flex: 1, backgroundColor: '#64748b' }]} onPress={handleResetServerUrl}>
                <Text style={styles.saveServerBtnText}>Reset ke Default</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
    position: 'relative',
  },
  // Ambient Mesh Gradient Blobs
  meshBlobTopRight: {
    position: 'absolute',
    top: -50,
    right: -50,
    width: 240,
    height: 240,
    borderRadius: 120,
    backgroundColor: '#38bdf8', // Sky blue
    opacity: 0.22,
  },
  meshBlobTopLeft: {
    position: 'absolute',
    top: 40,
    left: -60,
    width: 200,
    height: 200,
    borderRadius: 100,
    backgroundColor: '#34d399', // Emerald / Tosca
    opacity: 0.18,
  },
  meshBlobBottomLeft: {
    position: 'absolute',
    bottom: -60,
    left: -40,
    width: 260,
    height: 260,
    borderRadius: 130,
    backgroundColor: '#818cf8', // Indigo
    opacity: 0.18,
  },
  meshBlobBottomRight: {
    position: 'absolute',
    bottom: 80,
    right: -40,
    width: 180,
    height: 180,
    borderRadius: 90,
    backgroundColor: '#38bdf8', // Soft cyan
    opacity: 0.15,
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: 22,
    paddingVertical: 36,
  },
  header: {
    alignItems: 'center',
    marginBottom: 24,
  },
  logoContainer: {
    width: 90,
    height: 90,
    borderRadius: 45,
    backgroundColor: 'rgba(255, 255, 255, 0.95)',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.16,
    shadowRadius: 12,
    elevation: 4,
    borderWidth: 2,
    borderColor: 'rgba(255, 255, 255, 0.9)',
    padding: 6,
  },
  logoImage: {
    width: '100%',
    height: '100%',
  },
  brandTitle: {
    fontSize: 29,
    fontWeight: '800',
    color: '#0f172a',
    letterSpacing: 1.5,
  },
  badgePill: {
    backgroundColor: '#e0f2fe',
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 20,
    marginTop: 6,
    borderWidth: 1,
    borderColor: '#bae6fd',
  },
  badgeText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#0284c7',
    letterSpacing: 1,
  },
  brandDescription: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 6,
    textAlign: 'center',
  },
  card: {
    backgroundColor: 'rgba(255, 255, 255, 0.88)',
    borderRadius: 24,
    padding: 24,
    shadowColor: '#0f172a',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.08,
    shadowRadius: 20,
    elevation: 5,
    borderWidth: 1.5,
    borderColor: 'rgba(255, 255, 255, 0.95)',
  },
  formTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1e293b',
  },
  formSubtitle: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 4,
    marginBottom: 20,
    lineHeight: 18,
  },
  inputGroup: {
    marginBottom: 16,
  },
  label: {
    fontSize: 12.5,
    fontWeight: '600',
    color: '#334155',
    marginBottom: 6,
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 12,
    backgroundColor: 'rgba(248, 250, 252, 0.85)',
    paddingHorizontal: 12,
  },
  inputIcon: {
    marginRight: 8,
  },
  input: {
    flex: 1,
    paddingVertical: 12,
    fontSize: 14,
    color: '#0f172a',
  },
  eyeButton: {
    padding: 6,
  },
  loginButton: {
    backgroundColor: '#0284c7',
    borderRadius: 12,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 10,
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 4,
  },
  buttonDisabled: {
    opacity: 0.7,
  },
  buttonInner: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  buttonText: {
    color: '#ffffff',
    fontSize: 15,
    fontWeight: '700',
  },
  serverSettingBox: {
    marginTop: 14,
    padding: 12,
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#cbd5e1',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
  },
  serverLabel: {
    fontSize: 11,
    color: '#475569',
    fontWeight: '600',
    marginBottom: 4,
  },
  serverInput: {
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 8,
    paddingHorizontal: 10,
    paddingVertical: 6,
    fontSize: 12,
    color: '#0f172a',
  },
  saveServerBtn: {
    backgroundColor: '#0284c7',
    borderRadius: 8,
    paddingVertical: 8,
    alignItems: 'center',
  },
  saveServerBtnText: {
    color: '#ffffff',
    fontSize: 11.5,
    fontWeight: '600',
  },
  footerText: {
    textAlign: 'center',
    fontSize: 11,
    color: '#64748b',
    marginTop: 24,
  },
});
