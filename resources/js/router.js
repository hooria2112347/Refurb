// resources/js/router.js
import { createRouter, createWebHistory } from 'vue-router';

// Importing all components
import Home from './components/Home.vue';
import ItemDetail from './components/ItemDetail.vue';
import ArtistDetail from './components/ArtistDetail.vue';
import Login from './components/Login.vue';
import Signup from './components/Signup.vue';
import Dashboard from './components/Dashboard.vue';
import Collaboration from './components/Collaboration.vue';
import CustomRequest from './components/CustomRequest.vue';
import Portfolio from './components/Portfolio.vue';
import AddProduct from './components/AddProduct.vue';
import Feedback from './components/Feedback.vue';
import Account from './components/Account.vue';
import PasswordChange from './components/PasswordChange.vue';
import ManageProducts from './components/ManageProducts.vue';
import ForgetPassword from './components/ForgetPassword.vue'; // Import ForgetPassword component
import VerifyCode from '@/components/VerifyCode.vue';
import ResetPassword from '@/components/ResetPassword.vue';


// Define routes for each component
const routes = [
  { path: '/', component: Home },
  { path: '/item/:itemId', component: ItemDetail, props: true },
  { path: '/artist/:artistId', component: ArtistDetail, props: true },
  { path: '/login', component: Login },
  { path: '/add-product', component: AddProduct },
  { path: '/signup', component: Signup },
  { path: '/dashboard', component: Dashboard },
  { path: '/collaborations', component: Collaboration },
  { path: '/custom-request', component: CustomRequest },
  { path: '/portfolio', component: Portfolio },
  { path: '/manage-products', component: ManageProducts, name: 'ManageProducts' },
  {
    path: '/verify-code',
    name: 'VerifyCode',
    component: VerifyCode,
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword,
  },
  {
    path: "/account",
    name: "Account",
    component: Account,
  },
  {
    path: "/password-change",
    name: "PasswordChange",
    component: PasswordChange,
  },
  { path: '/feedback', component: Feedback },
  { path: '/forget-password', component: ForgetPassword }, // New route for Forget Password
  { path: '/:pathMatch(.*)*', redirect: '/' } // Redirect unknown routes to Home
];

const router = createRouter({
  history: createWebHistory(),
  routes
});


export default router;
