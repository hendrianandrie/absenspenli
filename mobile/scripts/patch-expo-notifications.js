const fs = require('fs');
const path = require('path');

const targetFile = path.join(
  __dirname,
  '..',
  'node_modules',
  'expo-notifications',
  'build',
  'TopicSubscriptionModule.android.js'
);

const safeContent = `import { requireOptionalNativeModule } from 'expo-modules-core';

let nativeModule = null;
try {
  nativeModule = requireOptionalNativeModule('ExpoTopicSubscriptionModule');
} catch (e) {
  nativeModule = null;
}

const fallbackModule = {
  addListener: () => {},
  removeListeners: () => {},
  subscribeToTopicAsync: () => Promise.resolve(null),
  unsubscribeFromTopicAsync: () => Promise.resolve(null),
};

export default nativeModule || fallbackModule;
`;

if (fs.existsSync(targetFile)) {
  fs.writeFileSync(targetFile, safeContent, 'utf8');
  console.log('[patch] Applied Expo Go safe fallback for TopicSubscriptionModule.android.js');
}
