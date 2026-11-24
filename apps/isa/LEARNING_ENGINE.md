# Learning Engine

## Propósito

El Learning Engine convierte interacciones en decisiones pedagógicas trazables. No delega el estado de aprendizaje ni la planificación al LLM.

## Ciclo

```text
Observar → evaluar → registrar evidencia → detectar patrón → priorizar
→ seleccionar objetivo → generar actividad → medir resultado → repetir
```

## Reglas

- El LLM propone; las políticas deterministas deciden persistencia y prioridad.
- Una respuesta aislada no cambia el nivel CEFR.
- Toda inferencia guarda confianza y procedencia.
- Las correcciones tienen categoría, gravedad, explicación y feedback del estudiante.
- Conversation corrige selectivamente; Teacher/Grammar explica más; Immersion prioriza inglés.
- Las felicitaciones deben depender de progreso o logro concreto.

## Entrada al LLM

Prompt Manager compone, en orden, identidad ISA, perfil pedagógico, modo, CEFR, objetivo actual, política de corrección, memoria relevante, resumen/ventana de sesión, entrada del usuario y contrato JSON.

El historial completo no se reinyecta. Se usa una ventana pequeña, resumen estructurado y memoria recuperada por objetivo, patrón y recencia.

## Salida validada

`TutorTurnResult` contiene texto de respuesta, correcciones, vocabulario candidato, evidencia de habilidad, evidencia CEFR y próxima acción. Valores desconocidos, largos o incoherentes se rechazan antes de escribir.

## Memoria y repetición

- `ErrorPattern` consolida errores repetidos por clave canónica.
- `SkillEvidence` acumula evidencia con confianza.
- `VocabularyItem` solo se crea si el término supera criterios de utilidad.
- `ReviewItem` y `ReviewLog` implementarán FSRS en Fase 10.
- Los ejercicios posteriores priorizan objetivos débiles, revisiones vencidas y metas activas.

## Resumen de sesión

Al finalizar, el motor produce fortalezas, puntos a trabajar, errores predominantes, vocabulario relevante y siguiente objetivo. El resumen se valida y se guarda como parte de la sesión.
