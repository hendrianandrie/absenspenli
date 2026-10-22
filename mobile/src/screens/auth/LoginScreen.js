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
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Header Branding */}
        <View style={styles.header}>
          <View style={styles.logoContainer}>
            <Image
              source={require('../../../assets/logo.png')}
              style={styles.logoImage}
              resizeMode="contain"
            />
          </View>
          <Text style={styles.brandTitle}>SI-KASEP</Text>
          <Text style={styles.brandSubtitle}>SMP NEGERI 5 CIAMIS</Text>
          <Text style={styles.brandDescription}>
            Sistem Informasi Rekap Absen dan Penilaian
          </Text>
        </View>

        {/* Form Card */}
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
            setDevTapCount(prev => {
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
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: 24,
    paddingVertical: 32,
  },
  header: {
    alignItems: 'center',
    marginBottom: 28,
  },
  logoContainer: {
    width: 88,
    height: 88,
    borderRadius: 44,
    backgroundColor: '#ffffff',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 14,
    shadowColor: '#0284c7',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.16,
    shadowRadius: 10,
    elevation: 4,
    borderWidth: 2,
    borderColor: '#e0f2fe',
    padding: 6,
  },
  logoImage: {
    width: '100%',
    height: '100%',
  },
  brandTitle: {
    fontSize: 28,
    fontWeight: '800',
    color: '#0f172a',
    letterSpacing: 1.5,
  },
  brandSubtitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0284c7',
    letterSpacing: 1,
    marginTop: 2,
  },
  brandDescription: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 4,
    textAlign: 'center',
  },
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 20,
    padding: 24,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.06,
    shadowRadius: 15,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#e2e8f0',
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
  serverInfoBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f1f5f9',
    borderRadius: 8,
    paddingVertical: 6,
    paddingHorizontal: 10,
    marginTop: 14,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  serverDot: {
    width: 7,
    height: 7,
    borderRadius: 4,
    backgroundColor: '#10b981',
    marginRight: 6,
  },
  serverInfoText: {
    fontSize: 11,
    color: '#64748b',
    flex: 1,
  },
  serverToggle: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 12,
    gap: 6,
  },
  serverToggleText: {
    fontSize: 11.5,
    color: '#64748b',
    fontWeight: '500',
  },
  serverSettingBox: {
    marginTop: 12,
    padding: 12,
    backgroundColor: '#f1f5f9',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#cbd5e1',
  },
  serverLabel: {
    fontSize: 11,
    color: '#475569',
    fontWeight: '600',
    marginBottom: 4,
  },
  serverInput: {
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 8,
    paddingHorizontal: 10,
    paddingVertical: 6,
    fontSize: 12,
    color: '#0f172a',
  },
  saveServerBtn: {
    backgroundColor: '#334155',
    borderRadius: 6,
    paddingVertical: 6,
    alignItems: 'center',
    marginTop: 8,
  },
  saveServerBtnText: {
    color: '#ffffff',
    fontSize: 11,
    fontWeight: '600',
  },
  footerText: {
    textAlign: 'center',
    fontSize: 11,
    color: '#94a3b8',
    marginTop: 24,
  },
});
