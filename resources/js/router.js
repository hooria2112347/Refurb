// resources/js/router.js
import { createRouter, createWebHistory } from 'vue-router';

// Importing all components
import UserProfile from './components/UserProfile.vue'; // Import the user profile component
import Home from './components/Home.vue';
import ProductDetails from './components/ProductDetails.vue';
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
import ViewCustomRequests from './components/ViewCustomRequests.vue';
import ArtistViewCustomRequests from './components/ArtistViewCustomRequests.vue';
import ViewAcceptedRequests from './components/ViewAcceptedRequests.vue';
import RequestDetailPage from './components/RequestDetailPage.vue';  
import BrowseScrap from './components/BrowseScrap.vue';
const routes = [  {
  path: '/user-profile/:id',
  name: 'UserProfile',
  component: UserProfile,
}, 
   {
      path: '/products/:id',  // Path for product details with dynamic ID
      name: 'product-details',
      component: ProductDetails,
      props: true, // Pass the `id` as a prop to the ProductDetails component
    }, {
  path: '/scrap-items',   // Route for browsing all scrap products
  name: 'browse-scrap',
  component: BrowseScrap,
},
  { path: '/', component: Home },
  // { path: '/item/:itemId', component: ItemDetail, props: true },
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
//for general/scrap user
  { path: '/view-custom-requests', component: ViewCustomRequests, name: 'ViewCustomRequests' },
//to view all the requests
  {
    path: '/artist-view-custom-requests',
    component: ArtistViewCustomRequests,
    // name: 'ArtistViewCustomRequests',
    // meta: { requiresAuth: true, role: 'artist' },
  },
  //to view the requests that the artist has accepted
  {
    path: '/view-accepted-requests',
    name: 'ViewAcceptedRequests',
    component: ViewAcceptedRequests,
    meta: { requiresAuth: true }, // Only authenticated users can access this page
  }
,  
{
  path: '/custom-requests/:id', // Dynamic route to view a single request's details
  name: 'RequestDetailPage',
  component: RequestDetailPage,
  props: true,
},

  { path: '/:pathMatch(.*)*', redirect: '/' } // Redirect unknown routes to Home
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
