import React, { useState, useEffect, useContext, useRef } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TouchableOpacity,
  ActivityIndicator,
  Platform,
  SafeAreaView,
} from 'react-native';
import { WebView } from 'react-native-webview';
import { Ionicons } from '@expo/vector-icons';
import { StatusBar } from 'expo-status-bar';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { AuthContext } from '../../context/AuthContext';
import { DEFAULT_BASE_URL, getRootWebUrl } from '../../config/api';

export default function StaffWebScreen({ navigation, route }) {
  const { token } = useContext(AuthContext);
  const { title = 'Portal Guru & Staf', path = '/dashboard' } = route.params || {};

  const webViewRef = useRef(null);
  const [targetUrl, setTargetUrl] = useState(null);
  const [loading, setLoading] = useState(true);
  const [canGoBack, setCanGoBack] = useState(false);
  const [loadError, setLoadError] = useState(null);

  useEffect(() => {
    const prepareUrl = async () => {
      try {
        const customUrl = await AsyncStorage.getItem('custom_base_url');
        const root = getRootWebUrl(customUrl || DEFAULT_BASE_URL);
        const url = `${root}/auth/mobile-bridge?token=${encodeURIComponent(token || '')}&redirect=${encodeURIComponent(path)}`;
        setTargetUrl(url);
      } catch (e) {
        const root = getRootWebUrl(DEFAULT_BASE_URL);
        setTargetUrl(`${root}/auth/mobile-bridge?token=${encodeURIComponent(token || '')}&redirect=${encodeURIComponent(path)}`);
      }
    };

    prepareUrl();
  }, [path, token]);

  const handleBack = () => {
    if (canGoBack && webViewRef.current) {
      webViewRef.current.goBack();
    } else {
      navigation.goBack();
    }
  };

  const handleReload = () => {
    setLoadError(null);
    if (webViewRef.current) {
      webViewRef.current.reload();
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" backgroundColor="#0284c7" />

      {/* Top Header Bar */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.headerBtn} onPress={handleBack} activeOpacity={0.7}>
          <Ionicons name="arrow-back" size={22} color="#ffffff" />
        </TouchableOpacity>

        <View style={styles.headerTitleWrap}>
          <Text style={styles.headerTitle} numberOfLines={1}>
            {title}
          </Text>
          <Text style={styles.headerSubtitle} numberOfLines={1}>
            Versi Web Terintegrasi
          </Text>
        </View>

        <View style={styles.headerActionRow}>
          <TouchableOpacity style={styles.headerBtn} onPress={handleReload} activeOpacity={0.7}>
            <Ionicons name="refresh" size={20} color="#ffffff" />
          </TouchableOpacity>
          <TouchableOpacity style={styles.headerBtn} onPress={() => navigation.goBack()} activeOpacity={0.7}>
            <Ionicons name="close" size={22} color="#ffffff" />
          </TouchableOpacity>
        </View>
      </View>

      {/* WebView Container */}
      <View style={styles.webContainer}>
        {targetUrl && (
          <WebView
            ref={webViewRef}
            source={{ uri: targetUrl }}
            style={styles.webView}
            javaScriptEnabled={true}
            domStorageEnabled={true}
            sharedCookiesEnabled={true}
            thirdPartyCookiesEnabled={true}
            scalesPageToFit={true}
            onLoadStart={() => setLoading(true)}
            onLoadEnd={() => setLoading(false)}
            onNavigationStateChange={(navState) => {
              setCanGoBack(navState.canGoBack);
            }}
            onError={(syntheticEvent) => {
              const { nativeEvent } = syntheticEvent;
              setLoadError(nativeEvent.description || 'Gagal memuat halaman web.');
              setLoading(false);
            }}
          />
        )}

        {/* Loading Spinner Indicator */}
        {loading && (
          <View style={styles.loadingOverlay}>
            <ActivityIndicator size="large" color="#0284c7" />
            <Text style={styles.loadingText}>Memuat Halaman {title}...</Text>
          </View>
        )}

        {/* Error Fallback */}
        {loadError && (
          <View style={styles.errorOverlay}>
            <Ionicons name="alert-circle-outline" size={48} color="#ef4444" />
            <Text style={styles.errorTitle}>Koneksi Gagal</Text>
            <Text style={styles.errorDesc}>{loadError}</Text>
            <TouchableOpacity style={styles.retryBtn} onPress={handleReload} activeOpacity={0.8}>
              <Ionicons name="refresh" size={16} color="#ffffff" style={{ marginRight: 6 }} />
              <Text style={styles.retryBtnText}>Coba Muat Ulang</Text>
            </TouchableOpacity>
          </View>
        )}
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#0284c7',
  },
  header: {
    height: 56,
    backgroundColor: '#0284c7',
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    justifyContent: 'space-between',
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 4,
  },
  headerBtn: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
    borderRadius: 20,
  },
  headerTitleWrap: {
    flex: 1,
    marginHorizontal: 8,
  },
  headerTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: '#ffffff',
  },
  headerSubtitle: {
    fontSize: 10.5,
    color: '#bae6fd',
    marginTop: 1,
  },
  headerActionRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  webContainer: {
    flex: 1,
    backgroundColor: '#f8fafc',
    position: 'relative',
  },
  webView: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  loadingOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(248, 250, 252, 0.85)',
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 10,
  },
  loadingText: {
    marginTop: 12,
    fontSize: 13,
    color: '#475569',
    fontWeight: '600',
  },
  errorOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: '#ffffff',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
    zIndex: 11,
  },
  errorTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#dc2626',
    marginTop: 12,
  },
  errorDesc: {
    fontSize: 12,
    color: '#64748b',
    textAlign: 'center',
    marginTop: 6,
    marginBottom: 20,
  },
  retryBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#0284c7',
    paddingHorizontal: 18,
    paddingVertical: 10,
    borderRadius: 10,
  },
  retryBtnText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#ffffff',
  },
});
