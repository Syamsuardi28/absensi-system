export function extractData(response, fallback = []) {
  const data = response?.data?.data
  if (Array.isArray(data)) return data
  if (data?.data && Array.isArray(data.data)) return data.data
  return fallback
}

export function extractPaginated(response) {
  return response?.data?.data?.data ?? []
}
