import axios from '../index'

export default {
  scan: (data) => axios.post('/attendance/scan', data),
  history: (params) => axios.get('/attendance/history', { params }),
  session: (data) => axios.post('/attendance/session', data),
  report: (params) => axios.get('/attendance/report', { params }),
}
