import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../pages/auth/LoginPage.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('../pages/dashboard/DashboardPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/attendance/scan',
    name: 'AttendanceScan',
    component: () => import('../pages/attendance/ScanPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/attendance/history',
    name: 'AttendanceHistory',
    component: () => import('../pages/attendance/HistoryPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/attendance/session',
    name: 'SessionAttendance',
    component: () => import('../pages/attendance/SessionAttendance.vue'),
    meta: { requiresAuth: true, roles: ['teacher'] },
  },
  {
    path: '/leave/request',
    name: 'LeaveRequest',
    component: () => import('../pages/leave/RequestPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/leave/approval',
    name: 'LeaveApproval',
    component: () => import('../pages/leave/ApprovalPage.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'admin', 'teacher'] },
  },
  {
    path: '/master/students',
    name: 'Students',
    component: () => import('../pages/master/StudentList.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'admin'] },
  },
  {
    path: '/master/teachers',
    name: 'Teachers',
    component: () => import('../pages/master/TeacherList.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'admin'] },
  },
  {
    path: '/master/classes',
    name: 'Classes',
    component: () => import('../pages/master/ClassList.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'admin'] },
  },
  {
    path: '/master/subjects',
    name: 'Subjects',
    component: () => import('../pages/master/SubjectList.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'admin'] },
  },
  {
    path: '/master/schedules',
    name: 'Schedules',
    component: () => import('../pages/master/ScheduleList.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'admin'] },
  },
  {
    path: '/master/school-years',
    name: 'SchoolYears',
    component: () => import('../pages/master/SchoolYearList.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'admin'] },
  },
  {
    path: '/qr/manage',
    name: 'QrManage',
    component: () => import('../pages/qr/QrManagePage.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'admin'] },
  },
  {
    path: '/reports',
    name: 'Reports',
    component: () => import('../pages/report/ReportPage.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'admin', 'teacher'] },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, _from, next) => {
  const auth = useAuthStore()

  if (to.meta.guest) {
    if (auth.isAuthenticated) {
      return next('/dashboard')
    }
    return next()
  }

  if (!auth.user) {
    try {
      await auth.fetchUser()
    } catch (err) {
      console.error('Failed to fetch user:', err)
    }
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }

  if (to.meta.roles && !to.meta.roles.some((r) => auth.user?.roles?.includes(r))) {
    return next('/dashboard')
  }

  next()
})

export default router
