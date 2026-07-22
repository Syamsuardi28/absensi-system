import axios from '../index'

export default {
  submit: (data) => axios.post('/leave-requests', data, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
  myRequests: (params) => axios.get('/leave-requests/my', { params }),
  pending: (params) => axios.get('/leave-requests/pending', { params }),
  approve: (id, data) => axios.patch(`/leave-requests/${id}/approve`, data),
  reject: (id, data) => axios.patch(`/leave-requests/${id}/reject`, data),
}
