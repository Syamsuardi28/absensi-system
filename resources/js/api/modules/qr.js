import axios from '../index'

export default {
  regenerate: (userId) => axios.post(`/qr/regenerate/${userId}`),
}
