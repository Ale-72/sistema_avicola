<?php

/**
 * Modelo de Calculadora - Completo
 */

class Calculadora
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Calcular requerimientos de alimento y agua
     */
    public function calcular($tipoAveId, $cantidadAves, $edadDias)
    {
        // Determinar la etapa según la edad
        $etapa = $this->getEtapaPorEdad($edadDias);

        if (!$etapa) {
            return [
                'error' => true,
                'mensaje' => 'No se encontró etapa para la edad especificada'
            ];
        }

        // Obtener parámetros nutricionales
        $parametros = $this->getParametros($tipoAveId, $etapa['id_etapa']);

        if (!$parametros) {
            return [
                'error' => true,
                'mensaje' => 'No se encontraron parámetros nutricionales para este tipo de ave y etapa'
            ];
        }

        // Calcular consumos totales
        $consumoAlimentoDiarioGr = $parametros['consumo_alimento_gr_dia'] * $cantidadAves;
        $consumoAguaDiarioMl = $parametros['consumo_agua_ml_dia'] * $cantidadAves;

        // Convertir a unidades más legibles
        $consumoAlimentoKg = round($consumoAlimentoDiarioGr / 1000, 2);
        $consumoAguaLitros = round($consumoAguaDiarioMl / 1000, 2);

        // Proyecciones 
        $consumoAlimentoSemanalKg = round($consumoAlimentoKg * 7, 2);
        $consumoAlimentoMensualKg = round($consumoAlimentoKg * 30, 2);
        $consumoAguaSemanalLitros = round($consumoAguaLitros * 7, 2);
        $consumoAguaMensualLitros = round($consumoAguaLitros * 30, 2);

        // Obtener datos del tipo de ave
        $tipoAve = $this->getTipoAveData($tipoAveId);

        return [
            'error' => false,
            'datos_entrada' => [
                'tipo_ave' => $tipoAve['nombre_tipo'],
                'proposito' => $tipoAve['proposito'],
                'cantidad_aves' => $cantidadAves,
                'edad_dias' => $edadDias,
                'nombre_etapa' => $etapa['nombre_etapa']
            ],
            'parametros' => [
                'consumo_ave_alimento_gr' => $parametros['consumo_alimento_gr_dia'],
                'consumo_ave_agua_ml' => $parametros['consumo_agua_ml_dia'],
                'proteina_porcentaje' => $parametros['proteina_porcentaje'],
                'energia_kcal' => $parametros['energia_kcal']
            ],
            'consumo_diario' => [
                'alimento_kg' => $consumoAlimentoKg,
                'agua_litros' => $consumoAguaLitros
            ],
            'proyecciones' => [
                'semanal' => [
                    'alimento_kg' => $consumoAlimentoSemanalKg,
                    'agua_litros' => $consumoAguaSemanalLitros
                ],
                'mensual' => [
                    'alimento_kg' => $consumoAlimentoMensualKg,
                    'agua_litros' => $consumoAguaMensualLitros
                ]
            ],
            'recomendaciones' => $this->getRecomendaciones($tipoAveId, $etapa['id_etapa'], $parametros)
        ];
    }

    /**
     * Obtener etapa por edad (privado)
     */
    private function getEtapaPorEdad($edadDias)
    {
        $query = "SELECT * FROM etapas_vida 
                  WHERE :edad BETWEEN edad_inicio_dias AND edad_fin_dias
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':edad', $edadDias);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obtener parámetros nutricionales (privado)
     */
    private function getParametros($tipoAveId, $etapaId)
    {
        $query = "SELECT * FROM parametros_nutricionales 
                  WHERE id_tipo_ave = :tipo AND id_etapa = :etapa";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tipo', $tipoAveId);
        $stmt->bindParam(':etapa', $etapaId);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obtener tipo de ave data (privado)
     */
    private function getTipoAveData($id)
    {
        $query = "SELECT * FROM tipos_ave WHERE id_tipo_ave = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obtener tipo de ave (público)
     */
    public function getTipoAve($id)
    {
        return $this->getTipoAveData($id);
    }

    /**
     * Obtener todos los tipos de ave
     */
    public function getTiposAve()
    {
        $query = "SELECT * FROM tipos_ave WHERE activo = 1 ORDER BY nombre_tipo ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll();
    }

    /**
     * Obtener todas las etapas de vida
     */
    public function getEtapas()
    {
        $query = "SELECT * FROM etapas_vida ORDER BY edad_inicio_dias ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll();
    }

    /**
     * Determinar etapa de vida (público)
     */
    public function determinarEtapaVida($tipoAveId, $edadDias)
    {
        return $this->getEtapaPorEdad($edadDias);
    }

    /**
     * Obtener parámetros nutricionales (público)
     */
    public function getParametrosNutricionales($tipoAveId, $etapaId = null)
    {
        if ($etapaId) {
            return $this->getParametros($tipoAveId, $etapaId);
        }

        // Obtener todos los parámetros para el tipo de ave
        $query = "SELECT pn.*, e.nombre_etapa, e.edad_inicio_dias, e.edad_fin_dias
                  FROM parametros_nutricionales pn
                  INNER JOIN etapas_vida e ON pn.id_etapa = e.id_etapa
                  WHERE pn.id_tipo_ave = :tipo
                  ORDER BY e.edad_inicio_dias ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tipo', $tipoAveId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener recomendaciones
     */
    private function getRecomendaciones($tipoAveId, $etapaId, $parametros)
    {
        $recomendaciones = [];

        // Recomendaciones según la etapa
        if ($etapaId == 1) { // Pollito 0-7 días
            $recomendaciones[] = "Mantener temperatura de 32-35°C en la primera semana";
            $recomendaciones[] = "Proporcionar alimento iniciador con alta proteína (min. 20%)";
            $recomendaciones[] = "Asegurar acceso permanente a agua limpia y fresca";
        } else if ($etapaId == 2) { // Iniciación 8-21 días
            $recomendaciones[] = "Reducir temperatura gradualmente a 28-30°C";
            $recomendaciones[] = "Continuar con alimento iniciador o de crecimiento";
        } else if ($etapaId >= 3) { // Crecimiento y acabado
            $recomendaciones[] = "Temperatura ambiente de 20-24°C es óptima";
            $recomendaciones[] = "Usar alimento de engorde con energía adecuada";
        }

        // Recomendación si es proteína específica
        if ($parametros['proteina_porcentaje']) {
            $recomendaciones[] = "El alimento debe contener al menos " . $parametros['proteina_porcentaje'] . "% de proteína";
        }

        return $recomendaciones;
    }

    /**
     * Guardar cálculo en historial
     */
    public function guardarHistorial($usuarioId, $tipoAveId, $etapaId, $cantidadAves, $edadDias, $resultados)
    {
        $query = "INSERT INTO historial_calculos 
                  (id_usuario, id_tipo_ave, id_etapa, cantidad_aves, edad_dias, resultado_alimento_kg, resultado_agua_litros)
                  VALUES (:usuario, :tipo, :etapa, :cantidad, :edad, :alimento, :agua)";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':usuario', $usuarioId);
        $stmt->bindParam(':tipo', $tipoAveId);
        $stmt->bindParam(':etapa', $etapaId);
        $stmt->bindParam(':cantidad', $cantidadAves);
        $stmt->bindParam(':edad', $edadDias);
        $stmt->bindParam(':alimento', $resultados['consumo_diario']['alimento_kg']);
        $stmt->bindParam(':agua', $resultados['consumo_diario']['agua_litros']);

        return $stmt->execute();
    }

    /**
     * Obtener historial de cálculos de usuario
     */
    public function getHistorial($usuarioId, $limit = 10)
    {
        $query = "SELECT hc.*, ta.nombre_tipo, ev.nombre_etapa
                  FROM historial_calculos hc
                  INNER JOIN tipos_ave ta ON hc.id_tipo_ave = ta.id_tipo_ave
                  INNER JOIN etapas_vida ev ON hc.id_etapa = ev.id_etapa
                  WHERE hc.id_usuario = :usuario
                  ORDER BY hc.fecha_calculo DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario', $usuarioId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener cálculo por ID
     */
    public function getCalculoById($id)
    {
        $query = "SELECT * FROM historial_calculos WHERE id_historial = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
}
