// assets/js/api.js

//Conexion para local
//const API_BASE_URL = "backend";

//Conexion para despliegue
const API_BASE_URL = "/backend";

export async function apiRequest(endpoint, method = "GET", data = null) {
  const headers = {
    "Content-Type": "application/json",
  };

  const token = localStorage.getItem("supabase_token");
  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
  }

  const config = {
    method,
    headers,
  };

  if (data) {
    config.body = JSON.stringify(data);
  }

  try {
    const response = await fetch(`${API_BASE_URL}/${endpoint}`, config);
    const responseData = await response.json();

    if (!response.ok) {
      // Log detallado para depuración (por ejemplo, errores de Mercado Pago)
      console.error("API Response Error:", {
        endpoint,
        status: response.status,
        data: responseData,
      });

      throw new Error(
        responseData.error?.message ||
          responseData.message ||
          responseData.error ||
          "API Request Failed"
      );
    }

    return responseData;
  } catch (error) {
    console.error("API Error:", error);
    throw error;
  }
}

