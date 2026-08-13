export function getErrorMessage(error: any): string {
  if (!error) return 'An unexpected error occurred. Please try again.';

  if (error.code === 'ERR_NETWORK' || error.message === 'Network Error') {
    return 'Unable to connect to the server. Please check your internet connection and try again.';
  }

  if (error.code === 'ECONNABORTED' || error.message?.includes('timeout')) {
    return 'Request timed out. Please try again.';
  }

  const response = error.response;
  if (!response) {
    return 'Unable to connect to the server. Please try again.';
  }

  const status = response.status;
  const data = response.data;

  if (status === 401) {
    return 'Email or password is incorrect.';
  }

  if (status === 403) {
    return "You don't have permission to perform this action.";
  }

  if (status === 404) {
    return 'We could not find the requested item.';
  }

  if (status === 429) {
    return 'Too many requests. Please wait a moment and try again.';
  }

  if (status === 422) {
    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0];
      if (firstKey && Array.isArray(data.errors[firstKey]) && data.errors[firstKey][0]) {
        return data.errors[firstKey][0];
      }
    }
    return data?.message || 'Please check the highlighted fields and try again.';
  }

  if (status >= 500) {
    return 'Something went wrong on our side. Please try again later.';
  }

  return data?.message || 'An error occurred. Please try again.';
}
