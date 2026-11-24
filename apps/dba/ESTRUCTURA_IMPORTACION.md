# Estructura para importar DBA

Se admiten archivos `.json`, `.csv` y `.txt`, con un máximo de 5 MB y 2.000 registros por archivo. Cada fila/objeto crea la jerarquía:

`Grado + Materia + Período → Estándar → DBA → Eje temático`

Los campos obligatorios son `grado`, `materia`, `periodo` y `estandar`. Los demás son opcionales.

| Campo | Requerido | Descripción |
|---|---|---|
| `grado` | Sí | Nombre ya creado en Guagua o su ID. Ej.: `Sexto` o `6`. |
| `materia` | Sí | Nombre de materia existente o su ID. Ej.: `Ciencias Sociales`. |
| `periodo` | Sí | Número entre `1` y `4`. |
| `estandar` | Sí | Nombre del estándar curricular. |
| `descripcion_estandar` | No | Descripción del estándar. |
| `dba` | No | Nombre del DBA asociado al estándar. |
| `descripcion_dba` | No | Descripción del DBA. |
| `eje_tematico` | No | Eje asociado al DBA. Requiere que `dba` tenga valor. |
| `descripcion_eje` | No | Descripción del eje temático. |

En CSV o TXT la primera línea debe contener los encabezados. CSV admite coma, punto y coma, tabulación o `|`; TXT usa la misma estructura. Para texto que contenga comas, use comillas dobles.

Ejemplo CSV:

```csv
grado,materia,periodo,estandar,descripcion_estandar,dba,descripcion_dba,eje_tematico,descripcion_eje
Sexto,Ciencias Sociales,1,"Comprendo las normas.",,"DBA 1: Reconoce normas.",,Convivencia,
```

Ejemplo JSON:

```json
{
  "registros": [
    {
      "grado": "Sexto",
      "materia": "Ciencias Sociales",
      "periodo": 1,
      "estandar": "Comprendo las normas.",
      "dba": "DBA 1: Reconoce normas.",
      "eje_tematico": "Convivencia"
    }
  ]
}
```

La importación no modifica ni duplica registros con el mismo nombre dentro de la misma relación. Si encuentra un error, no se guarda ningún registro de ese archivo.
