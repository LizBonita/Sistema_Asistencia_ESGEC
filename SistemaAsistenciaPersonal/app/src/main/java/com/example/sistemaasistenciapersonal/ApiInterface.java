package com.example.sistemaasistenciapersonal;

import com.example.sistemaasistenciapersonal.model.*;
import java.util.List;
import retrofit2.Call;
import retrofit2.http.*;

public interface ApiInterface {

    // ===========================
    // === LOGIN ===
    // ===========================
    @FormUrlEncoded
    @POST("login.php")
    Call<LoginResponse> login(
            @Field("usuario") String usuario,
            @Field("contrasena") String contrasena
    );

    // ===========================
    // === DASHBOARD STATS ===
    // ===========================
    @GET("get_dashboard_stats.php")
    Call<DashboardStats> getDashboardStats();

    // ===========================
    // === JUSTIFICACIONES ===
    // ===========================
    @GET("get_justificaciones_pendientes.php")
    Call<List<Justificacion>> getJustificacionesPendientes(@Query("id_maestro") int id);

    @GET("get_justificaciones_pendientes.php")
    Call<List<Justificacion>> getAllJustificacionesPendientes();

    // ===========================
    // === USUARIOS ===
    // ===========================
    @GET("get_usuarios.php")
    Call<List<Usuario>> getUsuarios();

    @GET("buscar_usuario.php")
    Call<List<Usuario>> buscarUsuario(@Query("texto") String texto);

    @POST("agregar_usuario.php")
    Call<ResponseUsuario> agregarUsuario(@Body Usuario usuario);

    @PUT("actualizar_usuario.php")
    Call<ResponseUsuario> actualizarUsuario(@Body Usuario usuario);

    @GET("eliminar_usuario.php")
    Call<ResponseUsuario> eliminarUsuario(@Query("id") int id);

    // ===========================
    // === MAESTROS ===
    // ===========================
    @GET("get_maestros.php")
    Call<List<Maestro>> getMaestros();

    // ===========================
    // === MATERIAS ===
    // ===========================
    @GET("get_materias.php")
    Call<List<Materia>> getMaterias();

    // ===========================
    // === GRUPOS ===
    // ===========================
    @GET("get_grupos.php")
    Call<List<Grupo>> getGrupos();

    // ===========================
    // === HORARIOS ===
    // ===========================
    @GET("get_horarios.php")
    Call<List<Horario>> getHorarios();

    // ===========================
    // === ASISTENCIAS DIARIAS ===
    // ===========================
    @GET("get_asistencias_diarias.php")
    Call<List<Asistencia>> getAsistenciasDiarias();

    @GET("get_asistencias_diarias.php")
    Call<List<Asistencia>> getAsistenciasPorFecha(@Query("fecha") String fecha);

    // ===========================
    // === REPORTE QUINCENAL ===
    // ===========================
    @GET("get_reporte_quincenal.php")
    Call<List<ReporteQuincenal>> getReporteQuincenal();

    @GET("get_reporte_quincenal.php")
    Call<List<ReporteQuincenal>> getReporteQuincenalPeriodo(
            @Query("inicio") String inicio,
            @Query("fin") String fin
    );
}