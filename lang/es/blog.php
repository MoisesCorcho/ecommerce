<?php

declare(strict_types=1);

return [
    'categories' => [
        'model' => [
            'label' => 'Categoría de blog',
            'plural' => 'Categorías de blog',
        ],
        'sections' => [
            'content' => 'Contenido bilingüe',
            'content_description' => 'Nombre y descripción traducibles de la categoría.',
            'settings' => 'Configuración y visibilidad',
            'settings_description' => 'Orden de prioridad y estado activo en la vitrina.',
        ],
        'fields' => [
            'name' => 'Nombre',
            'slug' => 'Slug',
            'slug_helper' => 'Identificador único en URL para filtrar artículos.',
            'description' => 'Descripción',
            'sort_order' => 'Orden de visualización',
            'sort_order_helper' => 'Menor número = mayor prioridad en pestañas y filtros.',
            'is_active' => 'Activa',
            'posts_count' => 'Artículos',
            'created_at' => 'Creada',
        ],
        'empty' => [
            'heading' => 'No hay categorías de blog todavía',
            'description' => 'Crea la primera categoría para organizar los artículos.',
        ],
        'notifications' => [
            'created' => 'Categoría de blog creada',
            'updated' => 'Categoría de blog actualizada',
        ],
    ],

    'posts' => [
        'model' => [
            'label' => 'Artículo',
            'plural' => 'Artículos',
        ],
        'sections' => [
            'content' => 'Contenido del artículo',
            'content_description' => 'Título, extracto y cuerpo del artículo en múltiples idiomas.',
            'settings' => 'Publicación y medios',
            'settings_description' => 'Categoría, estado de publicación, fecha y portada.',
            'seo' => 'Optimización para motores de búsqueda (SEO)',
            'seo_description' => 'Metatags personalizados para Google y previsualización en redes.',
        ],
        'badges' => [
            'primary' => 'Principal',
        ],
        'fields' => [
            'title' => 'Título',
            'slug' => 'Slug',
            'slug_helper' => 'Identificador canónico único en la URL `/blog/{slug}`.',
            'slug_tooltip' => 'URL amigable sin caracteres especiales ni espacios. Ej: el-arte-del-cuero-artesanal.',
            'excerpt' => 'Extracto',
            'excerpt_helper' => 'Breve resumen visible en el listado y tarjetas del blog.',
            'content' => 'Cuerpo del artículo',
            'cover_image' => 'Imagen de portada',
            'cover_image_helper' => 'Fotografía editorial principal (formato 16:10 o 4:3 recomendado).',
            'category' => 'Categoría',
            'author' => 'Autor',
            'status' => 'Estado',
            'published_at' => 'Fecha de publicación',
            'published_at_helper' => 'Si se programa a futuro, el artículo no será visible hasta esa fecha.',
            'meta_title' => 'Meta Título (SEO)',
            'meta_title_helper' => 'Opcional. Si está vacío, se usará el título del artículo.',
            'meta_title_tooltip' => 'Título optimizado para motores de búsqueda (50-60 caracteres recomendados). Ej: Guía para Cuidar Carteras de Cuero | Leen.',
            'meta_description' => 'Meta Descripción (SEO)',
            'meta_description_helper' => 'Opcional. Si está vacío, se usará el extracto del artículo.',
            'meta_description_tooltip' => 'Resumen persuasivo para resultados de búsqueda (140-160 caracteres recomendados). Incluye palabras clave relevantes.',
            'reading_time' => 'Tiempo estimado',
            'created_at' => 'Fecha de creación',
        ],
        'empty' => [
            'heading' => 'No hay artículos todavía',
            'description' => 'Escribe tu primera historia para compartirla con tus lectores.',
        ],
        'notifications' => [
            'created' => 'Artículo publicado o guardado',
            'updated' => 'Artículo actualizado',
        ],
    ],

    'storefront' => [
        'hero_title' => 'El Blog de Leen',
        'hero_subtitle' => 'Historias de artesanía, diseño y vida atemporal',
        'hero_description' => 'Explora nuestros artículos, guías de estilo y el universo artesanal detrás de cada una de nuestras piezas de cuero.',
        'all_categories' => 'Todos los artículos',
        'reading_time' => ':min min de lectura',
        'read_more' => 'Leer artículo',
        'published_on' => 'Publicado el :date',
        'by_author' => 'Por :author',
        'related_posts_heading' => 'Quizás también te interese',
        'related_posts_subtitle' => 'Descubre más historias y guías editoriales',
        'empty_heading' => 'Aún no hay artículos en esta sección',
        'empty_description' => 'Estamos redactando nuevas historias. Vuelve pronto para descubrir nuestras novedades.',
        'back_to_blog' => 'Volver al blog',
        'share' => 'Compartir historia',
        'preview_notice' => 'Modo Previsualización: Este artículo está en borrador o programado a futuro y solo es visible para administradores.',
        'show_more' => 'Ver más',
        'show_less' => 'Ver menos',
        'previous_categories' => 'Categorías anteriores',
        'next_categories' => 'Categorías siguientes',
        'search_placeholder' => 'Buscar historias, guías o temas...',
        'clear_search' => 'Limpiar búsqueda',
        'reset_filters' => 'Restablecer filtros',
        'no_search_results' => 'No encontramos ningún artículo que coincida con ":term".',
    ],
];
