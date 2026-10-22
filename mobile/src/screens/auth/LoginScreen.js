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
import { LinearGradient } from 'expo-linear-gradient';
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
      <StatusBar style="light" />

      {/* Full Linear Gradient Background (Pilihan 3) */}
      <LinearGradient
        colors={['#0369a1', '#0284c7', '#38bdf8']}
        start={{ x: 0.1, y: 0 }}
        end={{ x: 0.9, y: 1 }}
        style={StyleSheet.absoluteFillObject}
      />

      {/* Subtle overlay decorative circle for premium lighting */}
      <View style={styles.topGlow} pointerEvents="none" />
      <View style={styles.bottomGlow} pointerEvents="none" />

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
      >
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

        {/* Crisp Card over Gradient */}
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
    position: 'relative',
  },
  topGlow: {
    position: 'absolute',
    top: -60,
    right: -40,
    width: 200,
    height: 200,
    borderRadius: 100,
    backgroundColor: 'rgba(255, 255, 255, 0.15)',
  },
  bottomGlow: {
    position: 'absolute',
    bottom: -50,
    left: -40,
    width: 180,
    height: 180,
    borderRadius: 90,
    backgroundColor: 'rgba(255, 255, 255, 0.1)',
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
    width: 92,
    height: 92,
    borderRadius: 46,
    backgroundColor: '#ffffff',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.25,
    shadowRadius: 12,
    elevation: 8,
    borderWidth: 3,
    borderColor: '#ffffff',
    padding: 6,
  },
  logoImage: {
    width: '100%',
    height: '100%',
  },
  brandTitle: {
    fontSize: 30,
    fontWeight: '800',
    color: '#ffffff',
    letterSpacing: 2,
    textShadowColor: 'rgba(0, 0, 0, 0.2)',
    textShadowOffset: { width: 0, height: 2 },
    textShadowRadius: 6,
  },
  badgePill: {
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    paddingHorizontal: 14,
    paddingVertical: 4,
    borderRadius: 20,
    marginTop: 6,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.35)',
  },
  badgeText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#ffffff',
    letterSpacing: 1.2,
  },
  brandDescription: {
    fontSize: 12,
    color: '#e0f2fe',
    marginTop: 6,
    textAlign: 'center',
    lineHeight: 18,
    paddingHorizontal: 16,
  },
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 24,
    padding: 24,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.18,
    shadowRadius: 24,
    elevation: 8,
  },
  formTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#0f172a',
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
    borderColor: '#cbd5e1',
    borderRadius: 12,
    backgroundColor: '#f8fafc',
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
    shadowOpacity: 0.3,
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
    backgroundColor: 'rgba(255, 255, 255, 0.95)',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#cbd5e1',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 3,
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
    color: 'rgba(255, 255, 255, 0.85)',
    marginTop: 24,
    fontWeight: '500',
  },
});
