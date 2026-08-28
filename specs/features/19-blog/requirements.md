# F-01: Módulo de Blog Completo (CMS + Vistas Públicas)

> **Estado:** Specs en progreso  
> **ID:** F-01 · **Slug:** `19-blog`  
> **Prerequisitos:** Ninguno (módulo de contenido independiente)  
> **Desbloquea:** Posicionamiento orgánico (SEO), storytelling de marca y marketing de contenidos  
> **Presupuesto / Estimación:** 23 horas (184 €)

---

## 1. Fuentes canónicas (no duplicar)

| Tema | Fuente |
|------|--------|
| Reporte unificado de cliente | [`docs/cliente/Reporte Unificado Retroalimentacion y Scope.md`](../../../docs/cliente/Reporte%20Unificado%20Retroalimentacion%20y%20Scope.md) |
| Convenciones de arquitectura | `AGENTS.md` / `.ai/project-conventions rules` |
| Layout Storefront | `resources/views/layouts/storefront.blade.php` |

---

## 2. User Stories

1. **Como** creador de contenido / administrador de Leen, **quiero** redactar artículos con editor enriquecido (texto con formato, imágenes, listas), organizados por categorías bilingües y con control de estado (borrador/publicado), **para** publicar historias de marca y guías de estilo.
2. **Como** administrador, **quiero** configurar metadatos SEO (`meta_title`, `meta_description`) por artículo, **para** mejorar el posicionamiento en motores de búsqueda (Google).
3. **Como** visitante, **quiero** explorar la lista de artículos en `/blog` con paginación limpia y filtrado por categorías, **para** encontrar lecturas que me interesen.
4. **Como** visitante, **quiero** leer un artículo completo en `/blog/{slug}` con tipografía cuidada, imagen de portada y artículos recomendados al final, **para** disfrutar de una experiencia de lectura fluida.
5. **Como** visitante, **quiero** poder cambiar de idioma entre español e inglés y ver el contenido del artículo en el idioma seleccionado.

---

## 3. Criterios de Aceptación (EARS: R1 – Rn)

- **R1 (Publicación y Estados):**  
  *Cuando* un visitante ingrese a `/blog`,  
  *el sistema deberá* listar únicamente los artículos con `status = 'published'` y `published_at <= now()`, ordenados de forma descendente por `published_at`.

- **R2 (Acceso por Slug Único):**  
  *Cuando* se solicite la ruta `/blog/{slug}`,  
  *el sistema deberá* resolver el artículo correspondiente si está publicado.  
  *Si* el artículo está en estado borrador (`draft`) y el usuario no es un administrador autenticado,  
  *el sistema deberá* retornar un error HTTP 404.

- **R3 (Soporte Bilingüe i18n):**  
  *Cuando* se renderice el listado o el detalle,  
  *el sistema deberá* mostrar `title`, `excerpt` y `content` en el idioma activo (`es` o `en`), con fallback al idioma disponible si uno de los campos estuviera vacío.

- **R4 (Filtrado por Categorías):**  
  *Cuando* el visitante seleccione una categoría en `/blog`,  
  *el sistema deberá* filtrar los artículos publicados pertenecientes a esa categoría manteniendo la paginación.

- **R5 (Artículos Relacionados):**  
  *Al* final de cada artículo en `/blog/{slug}`,  
  *el sistema deberá* mostrar hasta 3 artículos relacionados de la misma categoría (excluyendo el artículo actual).

- **R6 (Panel Filament):**  
  *Cuando* un administrador ingrese al panel,  
  *el sistema deberá* ofrecer los recursos `PostResource` y `PostCategoryResource` con control de permisos, slugging automático, RichEditor y previsualización.

- **R7 (SEO Tags):**  
  *En* la cabecera HTML del detalle del artículo,  
  *el sistema deberá* inyectar las etiquetas OpenGraph y metatags configurados en el panel (`og:title`, `og:description`, `og:image`).

---

## 4. Alcance Excluido / Módulos Evolutivos (Por Cotizar)

Los siguientes ítems **no forman parte del alcance de esta entrega** y quedan documentados como siguientes pasos lógicos para cotización futura:

- **Auto-Traducción con API Externa (DeepL / Gemini / OpenAI):** Integración de un servicio de traducción automática que permita al administrador traducir artículos completos de español a inglés en 1 solo clic desde Filament. *(El módulo actual entrega la arquitectura JSON Translatable lista para traducción manual y fallback automático)*.
- **Comentarios públicos de lectores en artículos:** Sistema de comentarios y moderación de discusiones.
- **Multi-autor con perfiles públicos de blog:** Páginas de biografía de autores.
