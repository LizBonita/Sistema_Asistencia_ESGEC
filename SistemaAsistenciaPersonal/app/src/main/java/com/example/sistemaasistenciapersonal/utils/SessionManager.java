package com.example.sistemaasistenciapersonal.utils;

import android.content.Context;
import android.content.SharedPreferences;

public class SessionManager {
    private static final String PREF_NAME = "session";
    private static final String KEY_ID = "id";
    private static final String KEY_ROL = "rol";
    private static final String KEY_NOMBRE = "nombre";

    private SharedPreferences prefs;

    public SessionManager(Context context) {
        prefs = context.getSharedPreferences(PREF_NAME, Context.MODE_PRIVATE);
    }

    public void saveSession(int id, String rol, String nombre) {
        SharedPreferences.Editor editor = prefs.edit();
        editor.putInt(KEY_ID, id);
        editor.putString(KEY_ROL, rol);
        editor.putString(KEY_NOMBRE, nombre);
        editor.apply();
    }

    // Soporte int rol
    public void saveSession(int id, int rol, String nombre) {
        saveSession(id, String.valueOf(rol), nombre);
    }

    public int getUserId() {
        return prefs.getInt(KEY_ID, -1);
    }

    public String getUserRol() {
        return prefs.getString(KEY_ROL, null);
    }

    public String getUserNombre() {
        return prefs.getString(KEY_NOMBRE, "");
    }

    public boolean isLoggedIn() {
        return getUserId() != -1;
    }

    public boolean isAdmin() {
        String rol = getUserRol();
        return "1".equals(rol) || "4".equals(rol);
    }

    public boolean isMaestro() {
        return "3".equals(getUserRol());
    }

    public void clearSession() {
        SharedPreferences.Editor editor = prefs.edit();
        editor.clear();
        editor.apply();
    }
}