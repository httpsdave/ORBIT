import { initializeApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';

// Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyBA6zYDJHH_VElAkDlSxL8MKGf5zXGmbK4",
  authDomain: "orbit-f53d6.firebaseapp.com",
  projectId: "orbit-f53d6",
  storageBucket: "orbit-f53d6.firebasestorage.app",
  messagingSenderId: "17147288253",
  appId: "1:17147288253:web:f8283825384c064cd1b5d4"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);

// Initialize Firebase Authentication and get a reference to the service
export const auth = getAuth(app);
export default app;