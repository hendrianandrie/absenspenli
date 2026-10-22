import React, { useState, useEffect, useContext } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TouchableOpacity,
  ActivityIndicator,
  Platform,
  PermissionsAndroid,
  Alert,
} from 'react-native';
import { WebView } from 'react-native-webview';
import { Ionicons } from '@expo/vector-icons';
import { StatusBar } from 'expo-status-bar';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { AuthContext } from '../../context/AuthContext';
import { DEFAULT_BASE_URL, getRootWebUrl } from '../../config/api';

export default function FaceScannerScreen({ navigation }) {
  const { token } = useContext(AuthContext);
  const [scannerUrl, setScannerUrl] = useState(null);
  const [hasPermission, setHasPermission] = useState(false);
  const [loading, setLoading] = useState(true);

  // 1. Minta Izin Kamera di Android sebelum WebView dimuat
  useEffect(() => {
    const requestCameraPermission = async () => {
      if (Platform.OS === 'android') {
        try {
          const granted = await PermissionsAndroid.requestMultiple([
            PermissionsAndroid.PERMISSIONS.CAMERA,
            PermissionsAndroid.PERMISSIONS.RECORD_AUDIO,
          ]);
          if (
            granted[PermissionsAndroid.PERMISSIONS.CAMERA] ===
            PermissionsAndroid.RESULTS.GRANTED
          ) {
            setHasPermission(true);
          } else {
            Alert.alert(
              'Izin Kamera Ditolak',
              'Akses kamera diperlukan untuk memindai presensi wajah siswa.'
            );
          }
        } catch (err) {
          console.warn('Gagal meminta izin kamera:', err);
        }
      } else {
        setHasPermission(true);
      }
    };

    requestCameraPermission();
  }, []);

  // 2. Susun Alamat URL Scanner
  useEffect(() => {
    const prepareUrl = async () => {
      try {
        const customUrl = await AsyncStorage.getItem('custom_base_url');
        const root = getRootWebUrl(customUrl || DEFAULT_BASE_URL);
        const url = `${root}/face-recognition/mobile-scanner?token=${encodeURIComponent(token || '')}`;
        setScannerUrl(url);
      } catch (e) {
        const root = getRootWebUrl(DEFAULT_BASE_URL);
        setScannerUrl(`${root}/face-recognition/mobile-scanner?token=${encodeURIComponent(token || '')}`);
      } finally {
        setLoading(false);
      }
    };

    if (token) {
      prepareUrl();
    }
  }, [token]);

  if (loading || !scannerUrl || !hasPermission) {
    return (
      <View style={styles.centerContainer}>
        <StatusBar style="light" />
        <ActivityIndicator size="large" color="#0284c7" />
        <Text style={styles.centerText}>
          {!hasPermission ? 'Menyiapkan Izin Kamera...' : 'Menyiapkan Scanner Presensi...'}
        </Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <StatusBar style="light" />

      {/* Top Header Bar */}
      <View style={styles.headerBar}>
        <TouchableOpacity
          style={styles.backBtn}
          onPress={() => navigation.goBack()}
          activeOpacity={0.7}
        >
          <Ionicons name="arrow-back" size={22} color="#ffffff" />
        </TouchableOpacity>
        <View style={styles.headerInfo}>
          <Text style={styles.headerTitle}>Scanner Presensi Wajah</Text>
          <Text style={styles.headerSubtitle}>Kamera Khusus Petugas Piket</Text>
        </View>
        <TouchableOpacity
          style={styles.reloadBtn}
          onPress={() => {
            // Force reload URL
            setScannerUrl((prev) => `${prev}&t=${Date.now()}`);
          }}
          activeOpacity={0.7}
        >
          <Ionicons name="refresh" size={20} color="#ffffff" />
        </TouchableOpacity>
      </View>

      {/* Embedded Live Camera Scanner */}
      <View style={styles.webViewContainer}>
        <WebView
          source={{ uri: scannerUrl }}
          allowsInlineMediaPlayback={true}
          mediaPlaybackRequiresUserAction={false}
          mediaCapturePermissionGrantType="grant"
          androidHardwareAccelerationDisabled={false}
          javaScriptEnabled={true}
          domStorageEnabled={true}
          originWhitelist={['*']}
          mixedContentMode="always"
          allowsFullscreenVideo={false}
          onPermissionRequest={(event) => {
            const req = event?.nativeEvent || event;
            if (req && typeof req.grant === 'function') {
              req.grant(req.resources);
            } else if (typeof event?.grant === 'function') {
              event.grant(event.resources);
            }
          }}
          style={styles.webView}
          startInLoadingState={true}
          renderLoading={() => (
            <View style={styles.loadingOverlay}>
              <ActivityIndicator size="large" color="#38bdf8" />
              <Text style={styles.loadingOverlayText}>Memuat Modul AI Kamera...</Text>
            </View>
          )}
        />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#070b13',
  },
  centerContainer: {
    flex: 1,
    backgroundColor: '#070b13',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  centerText: {
    marginTop: 14,
    color: '#94a3b8',
    fontSize: 14,
    fontWeight: '600',
  },
  headerBar: {
    backgroundColor: '#0369a1',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingTop: Platform.OS === 'ios' ? 52 : 40,
    paddingBottom: 14,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255, 255, 255, 0.15)',
    gap: 12,
  },
  backBtn: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: 'rgba(255, 255, 255, 0.15)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  reloadBtn: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: 'rgba(255, 255, 255, 0.15)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerInfo: {
    flex: 1,
  },
  headerTitle: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '800',
    letterSpacing: 0.3,
  },
  headerSubtitle: {
    color: '#bae6fd',
    fontSize: 11,
    fontWeight: '600',
    marginTop: 1,
  },
  webViewContainer: {
    flex: 1,
    position: 'relative',
    backgroundColor: '#000000',
  },
  webView: {
    flex: 1,
    backgroundColor: '#000000',
  },
  loadingOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: '#070b13',
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingOverlayText: {
    color: '#e0f2fe',
    fontSize: 13,
    fontWeight: '600',
    marginTop: 12,
  },
});
