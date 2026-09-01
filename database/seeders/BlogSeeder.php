<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Blog\PostStatusEnum;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->where('email', 'admin@admin.com')->first()
            ?? User::query()->first()
            ?? User::factory()->create([
                'name' => 'Equipo Leen',
                'last_name' => '',
                'email' => 'admin@admin.com',
            ]);

        $this->seedBlogImages();

        // 1. Categorías del Blog
        $catPanal = PostCategory::query()->updateOrCreate(
            ['slug' => 'inspiracion-panal'],
            [
                'name' => [
                    'es' => 'Inspiración Panal',
                    'en' => 'Honeycomb Inspiration',
                ],
                'description' => [
                    'es' => 'Geometría orgánica, dulzura y la naturaleza como fuente de diseño.',
                    'en' => 'Organic geometry, sweetness, and nature as a design source.',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $catArtesania = PostCategory::query()->updateOrCreate(
            ['slug' => 'artesania-cuero'],
            [
                'name' => [
                    'es' => 'El Arte del Cuero',
                    'en' => 'Leather Craftsmanship',
                ],
                'description' => [
                    'es' => 'Historias detrás de la selección de pieles y técnicas tradicionales de marroquinería.',
                    'en' => 'Stories behind leather selection and traditional leathercraft techniques.',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $catEstilo = PostCategory::query()->updateOrCreate(
            ['slug' => 'estilo-tendencias'],
            [
                'name' => [
                    'es' => 'Estilo & Tendencias',
                    'en' => 'Style & Trends',
                ],
                'description' => [
                    'es' => 'Guías de combinación, versatilidad y estética contemporánea.',
                    'en' => 'Styling guides, versatility, and contemporary aesthetics.',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $catCuidado = PostCategory::query()->updateOrCreate(
            ['slug' => 'cuidado-longevidad'],
            [
                'name' => [
                    'es' => 'Cuidado & Longevidad',
                    'en' => 'Care & Longevity',
                ],
                'description' => [
                    'es' => 'Consejos esenciales para preservar la pátina y belleza de tus piezas.',
                    'en' => 'Essential tips to preserve the patina and beauty of your leather pieces.',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        // 2. Colección de 20 Artículos del Blog
        $posts = [
            [
                'slug' => 'la-geometria-viva-el-panal-como-manifiesto-de-diseno',
                'category_id' => $catPanal->id,
                'cover_image_path' => 'blog/05.jpeg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(1),
                'title' => [
                    'es' => 'La Geometría Viva: El Panal como Manifiesto de Diseño',
                    'en' => 'Living Geometry: The Honeycomb as a Design Manifesto',
                ],
                'excerpt' => [
                    'es' => 'Exploramos cómo las estructuras hexagonales de las abejas inspiran la arquitectura flexible y la resistencia de nuestra colección icónica.',
                    'en' => 'We explore how hexagonal bee structures inspire the flexible architecture and resilience of our iconic collection.',
                ],
                'content' => [
                    'es' => '<p>En el corazón de Leen existe una fascinación inextinguible por la naturaleza y sus patrones constructivos más perfectos. El panal no es únicamente una forma geométrica; es un manifiesto de eficiencia orgánica, resistencia estructural y armonía visual.</p>
<p>Cuando concebimos nuestras primeras siluetas inspiradas en panales, buscábamos trasladar esa misma sensación de refugio y calidez: convertir pieles seleccionadas en panales que no solo transportan tus objetos cotidianos, sino que encapsulan una historia de intención y detalle.</p>
<h3>El Hexágono en la Marroquinería Contemporánea</h3>
<p>A diferencia de las líneas rígidas tradicionales, la estructura de panal permite un juego sutil de luces y sombras que realza la textura natural del cuero. Cada costura sigue un ritmo consciente, garantizando que la pieza mantenga su silueta escultórica tanto en movimiento como en reposo.</p>
<blockquote>«Convertimos pieles en panales: una promesa de artesanía que abraza lo dulce y lo duradero.»</blockquote>
<p>Llevar una pieza inspirada en el panal es portar un recordatorio de que la verdadera sofisticación reside en la armonía entre forma, función y respeto por los ritmos de la naturaleza.</p>',
                    'en' => '<p>At the heart of Leen lies an inextinguishable fascination with nature and its most flawless construction patterns. The honeycomb is not merely a geometric form; it is a manifesto of organic efficiency, structural resilience, and visual harmony.</p>
<p>When designing our first honeycomb-inspired silhouettes, we aimed to convey that exact sensation of warmth and shelter: transforming carefully chosen hides into honeycombs that carry your daily essentials while embodying a narrative of intention.</p>
<h3>The Hexagon in Contemporary Leathercraft</h3>
<p>Unlike rigid conventional lines, honeycomb architecture fosters a delicate interplay of light and shadow that emphasizes the leather’s organic texture. Every seam follows a conscious rhythm, ensuring the piece preserves its sculptural silhouette.</p>
<blockquote>“We turn hides into honeycombs: a promise of craftsmanship that embraces sweetness and longevity.”</blockquote>
<p>Carrying a honeycomb piece is holding a tangible reminder that true elegance blooms where form, function, and respect for nature’s pace unite.</p>',
                ],
                'meta_title' => [
                    'es' => 'La Geometría Viva: El Panal en el Diseño de Carteras | Leen',
                    'en' => 'Living Geometry: Honeycomb in Handbag Design | Leen',
                ],
                'meta_description' => [
                    'es' => 'Descubre cómo los hexágonos y el panal de abejas inspiran la estética, resistencia y arquitectura de las carteras de cuero artesanales de Leen.',
                    'en' => 'Discover how honeycombs inspire the aesthetic, durability, and architecture of Leen handcrafted leather handbags.',
                ],
            ],

            [
                'slug' => 'el-alma-de-una-cartera-como-elegir-tu-pieza-ideal',
                'category_id' => $catEstilo->id,
                'cover_image_path' => 'blog/09.jpeg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(3),
                'title' => [
                    'es' => 'El Alma de una Cartera: Cómo Elegir la Pieza que Contará Tu Historia',
                    'en' => 'The Soul of a Handbag: Choosing the Piece to Carry Your Story',
                ],
                'excerpt' => [
                    'es' => 'Una cartera no es un simple accesorio; es el contenedor de tus secretos, proyectos y momentos memorables. Te guiamos para elegir tu compañera ideal.',
                    'en' => 'A handbag is not merely an accessory; it is the keeper of your dreams and daily milestones. Here is our guide to finding your perfect piece.',
                ],
                'content' => [
                    'es' => '<p>Dicen que para conocer a una persona basta con observar lo que lleva en su bolso. Pero más allá de lo que contiene, la elección de la cartera misma revela la relación que tenemos con nuestro propio tiempo y estilo.</p>
<p>Desde siluetas compactas pensadas para la ligereza nocturna hasta bolsos estructurados con capacidad para días de trabajo creativo, cada diseño de Leen nace para adaptarse a la vida en movimiento sin perder un ápice de elegancia.</p>
<h3>Proporciones, Colores y Versatilidad</h3>
<p>Al elegir una pieza, evalúa cómo dialoga con tu paleta de colores habitual. Los tonos tierra —como nuestro Intense Cocoa y Soft Sand— ofrecen una neutralidad sofisticada que trasciende las temporadas y envejece con una pátina única.</p>
<p>Opta por asas ajustables si valoras la versatilidad de llevarla al hombro o cruzada, y asegúrate de que el cierre y los herrajes proporcionen la seguridad necesaria para tus trayectos diarios.</p>',
                    'en' => '<p>They say you can tell a person’s essence by looking at what they carry in their bag. Yet beyond the contents, the choice of the handbag itself reveals our relationship with personal style and time.</p>
<p>From compact silhouettes tailored for evening grace to structured totes designed for dynamic creative days, each Leen creation adapts seamlessly to modern life.</p>
<h3>Proportions, Tones, and Versatility</h3>
<p>When selecting a handbag, consider how it interacts with your everyday wardrobe. Warm earthy tones like Intense Cocoa and Soft Sand deliver an understated luxury that defies fast-fashion trends and grows richer with time.</p>',
                ],
                'meta_title' => [
                    'es' => 'Cómo Elegir la Cartera de Cuero Ideal | Leen Journal',
                    'en' => 'How to Choose Your Ideal Leather Handbag | Leen Journal',
                ],
                'meta_description' => [
                    'es' => 'Guía de estilo y proporciones para encontrar la cartera de cuero perfecta según tus necesidades cotidianas y estética personal.',
                    'en' => 'Style and proportion guide to discovering the ideal leather handbag for your daily routine and personal aesthetic.',
                ],
            ],

            [
                'slug' => 'secretos-de-taller-cuidado-del-cuero-y-su-patina',
                'category_id' => $catCuidado->id,
                'cover_image_path' => 'blog/14.jpeg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(6),
                'title' => [
                    'es' => 'Secretos de Taller: El Cuidado del Cuero y el Cultivo de su Pátina',
                    'en' => 'Workshop Secrets: Caring for Leather and Nurturing Its Patina',
                ],
                'excerpt' => [
                    'es' => 'Consejos prácticos de nuestros artesanos para hidratar, limpiar y proteger tus bolsos de cuero, asegurando que adquieran carácter con los años.',
                    'en' => 'Practical tips from our artisans to condition, clean, and protect your leather bags, ensuring they age with grace and character.',
                ],
                'content' => [
                    'es' => '<p>El cuero genuino es un material orgánico y noble. Con el uso cotidiano, la exposición a la luz y el contacto con tus manos, desarrolla una pátina irrepetible que refleja el paso de tu propia vida.</p>
<h3>Tres Reglas de Oro para Preservar tu Pieza</h3>
<p><strong>1. Hidratación periódica:</strong> Aplica una crema acondicionadora neutra para cuero con un paño de algodón suave cada tres o cuatro meses para evitar que la piel pierda sus aceites naturales.</p>
<p><strong>2. Almacenamiento consciente:</strong> Cuando no utilices tu cartera, guárdala siempre en su bolsa guardapolvo de tela y rellénala con papel seda sin ácido para conservar su estructura.</p>
<p><strong>3. Protección contra el agua y calor:</strong> Si la pieza entra en contacto con lluvia, sécala inmediatamente con toques suaves usando un paño seco. Jamás utilices secadores ni la expongas al sol directo para secarla.</p>',
                    'en' => '<p>Genuine leather is a noble, organic material. Through regular use, natural light, and handling, it builds a unique patina that tells your personal journey.</p>
<h3>Three Golden Rules for Leather Longevity</h3>
<p><strong>1. Periodic conditioning:</strong> Apply a neutral leather nourishing balm with a soft cloth every 3-4 months to replenish essential natural oils.</p>
<p><strong>2. Thoughtful storage:</strong> Store your bag in its protective cotton dust bag, filled with tissue paper to preserve its sculptural contour.</p>
<p><strong>3. Guarding against water and heat:</strong> If caught in rain, gently dab dry with a microfiber cloth and let air dry naturally away from direct heat sources.</p>',
                ],
                'meta_title' => [
                    'es' => 'Guía de Cuidado y Mantenimiento del Cuero | Leen',
                    'en' => 'Leather Care & Maintenance Guide | Leen',
                ],
                'meta_description' => [
                    'es' => 'Aprende a cuidar, hidratar y limpiar tus carteras de cuero artesanal para que duren toda la vida con los consejos de los artesanos de Leen.',
                    'en' => 'Learn how to nourish, clean, and preserve your handcrafted leather bags with essential advice from Leen master craftsmen.',
                ],
            ],

            [
                'slug' => 'slow-fashion-y-marroquineria-colombiana',
                'category_id' => $catArtesania->id,
                'cover_image_path' => 'blog/17.jpg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(9),
                'title' => [
                    'es' => 'Slow Fashion y Marroquinería Colombiana: Creando con Intención',
                    'en' => 'Slow Fashion & Colombian Leathercraft: Creating with Intention',
                ],
                'excerpt' => [
                    'es' => 'Conoce el proceso detrás de cada corte, biselado y costura artesanal en nuestros talleres colombianos, donde el tiempo es el ingrediente del lujo.',
                    'en' => 'Discover the meticulous cutting, burnishing, and stitching in our Colombian workshops, where time is the true luxury.',
                ],
                'content' => [
                    'es' => '<p>Frente al vértigo de la producción masiva, en Leen elegimos el camino de la pausa y la maestría manual. Cada bolso requiere horas de dedicación por parte de artesanos locales que han perfeccionado técnicas de confección transmitidas por generaciones.</p>
<p>Trabajar bajo el modelo de slow fashion significa producir lotes limitados, aprovechar al máximo cada corte de piel y garantizar condiciones justas y dignas para cada persona involucrada en la cadena de creación.</p>
<blockquote>«El verdadero lujo no es lo inmediato; es aquello que fue creado con paciencia, maestría y respeto.»</blockquote>
<p>Nuestra manufactura 100% colombiana celebra la riqueza del talento local, fusionando la herencia tradicional con una visión estética global y refinada.</p>',
                    'en' => '<p>Amid the frenzy of mass production, Leen champions conscious pacing and manual mastery. Each piece requires dedicated hours by local artisans who uphold time-honored leathercraft traditions.</p>
<p>Adopting slow fashion principles means crafting limited batches, minimizing leather waste, and ensuring dignified working conditions across the entire creative circle.</p>
<blockquote>“True luxury is never rushed; it is what is crafted with patience, mastery, and reverence.”</blockquote>
<p>Our 100% Colombian manufacturing honors local talent, blending rich heritage with a refined global aesthetic.</p>',
                ],
                'meta_title' => [
                    'es' => 'Slow Fashion y Artesanía en Cuero Colombiano | Leen',
                    'en' => 'Slow Fashion and Colombian Leathercraft | Leen',
                ],
                'meta_description' => [
                    'es' => 'Conoce cómo en Leen combinamos el talento artesanal colombiano con los principios de la moda consciente y sostenible.',
                    'en' => 'Explore how Leen merges Colombian artisan talent with conscious, sustainable luxury principles.',
                ],
            ],

            [
                'slug' => 'de-la-pasion-a-la-marca-nuestra-historia',
                'category_id' => $catArtesania->id,
                'cover_image_path' => 'blog/18.jpg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(12),
                'title' => [
                    'es' => 'De la Pasión a la Marca: La Historia Detrás de Leen',
                    'en' => 'From Passion to Brand: The Story Behind Leen',
                ],
                'excerpt' => [
                    'es' => 'La travesía de convertir un proyecto personal universitario en una marca que viste de miel y elegancia a personas en todo el mundo.',
                    'en' => 'The journey of turning a personal university dream into a brand dressing people around the world with honey and elegance.',
                ],
                'content' => [
                    'es' => '<p>Leen nació en las aulas universitarias, entre bocetos de moda, notas de arte y el deseo genuino de crear piezas que una misma desearía portar todos los días. Lo que comenzó como un proyecto íntimo se convirtió rápidamente en un movimiento de personas que valoran la autenticidad sobre lo efímero.</p>
<p>El nombre y la identidad de Leen están inspirados en la miel: símbolo de dedicación comunitaria, dulzura y alquimia natural. Cada cartera es el resultado de cientos de pequeñas decisiones enfocadas en la excelencia.</p>
<p>Hoy, esa visión sigue guiando cada paso: vestir a personas de todo el mundo con piezas hechas para cargar historias, recuerdos y propósitos.</p>',
                    'en' => '<p>Leen was born in university halls, between fashion sketches, art notes, and a heartfelt desire to create pieces one would truly love to carry every day. What started as an intimate pursuit soon evolved into a movement of individuals who value authenticity over the ephemeral.</p>
<p>The name and spirit of Leen are inspired by honey: a timeless symbol of community devotion, sweetness, and natural alchemy.</p>
<p>Today, that foundational vision remains: dressing people worldwide with pieces crafted to carry their stories and aspirations.</p>',
                ],
                'meta_title' => [
                    'es' => 'Nuestra Historia: El Origen de la Marca Leen',
                    'en' => 'Our Story: The Origin of the Leen Brand',
                ],
                'meta_description' => [
                    'es' => 'Conoce el origen de Leen, una marca colombiana de carteras y accesorios artesanales creada para vestir de miel al mundo.',
                    'en' => 'Learn about the origin of Leen, a Colombian handcrafted handbag and accessory brand designed to dress the world with honey.',
                ],
            ],

            [
                'slug' => 'la-arquitectura-del-hexagono-fuerza-y-flexibilidad',
                'category_id' => $catPanal->id,
                'cover_image_path' => 'blog/banner-8.jpg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(15),
                'title' => [
                    'es' => 'La Arquitectura del Hexágono: Fuerza y Flexibilidad en el Cuero',
                    'en' => 'Hexagon Architecture: Strength and Flexibility in Leather',
                ],
                'excerpt' => [
                    'es' => 'Por qué la geometría de seis lados es la solución estructural perfecta para carteras livianas pero increíblemente resistentes.',
                    'en' => 'Why six-sided geometry is the ultimate structural solution for lightweight yet remarkably durable leather bags.',
                ],
                'content' => [
                    'es' => '<p>En la física de los materiales, el hexágono es conocido como el polígono más eficiente: maximiza el volumen contenido mientras minimiza el perímetro y el peso del material. En Leen, aplicamos este principio matemático al patronaje de marroquinería de alta gama.</p>
<p>Al ensamblar paneles hexagonales, la tensión se distribuye de manera uniforme a lo largo de las costuras, evitando deformaciones en puntos críticos y permitiendo que la cartera mantenga su porte sin requerir refuerzos sintéticos pesados.</p>',
                    'en' => '<p>In materials science, the hexagon is celebrated as the most efficient polygon: maximizing interior volume while minimizing perimeter and weight. At Leen, we translate this natural principle into sophisticated patternmaking.</p>',
                ],
                'meta_title' => [
                    'es' => 'Arquitectura Hexagonal en Carteras de Lujo | Leen',
                    'en' => 'Hexagonal Architecture in Luxury Handbags | Leen',
                ],
                'meta_description' => [
                    'es' => 'Descubre por qué las siluetas hexagonales ofrecen la combinación perfecta de peso pluma y máxima durabilidad.',
                    'en' => 'Discover how hexagonal design delivers the ideal balance of featherlight feel and enduring structure.',
                ],
            ],

            [
                'slug' => 'tonos-tierra-el-lenguaje-del-intense-cocoa-y-soft-sand',
                'category_id' => $catEstilo->id,
                'cover_image_path' => 'blog/banner-9.jpg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(18),
                'title' => [
                    'es' => 'Tonos Tierra: El Lenguaje del Intense Cocoa y Soft Sand',
                    'en' => 'Earthy Tones: The Language of Intense Cocoa and Soft Sand',
                ],
                'excerpt' => [
                    'es' => 'Cómo los colores neutros y cálidos construyen una presencia sofisticada sin necesidad de recurrir a estridencias visuales.',
                    'en' => 'How warm, neutral hues build an aura of refined quiet luxury without visual noise.',
                ],
                'content' => [
                    'es' => '<p>El color es emoción pura. Nuestra paleta insignia se nutre de los elementos orgánicos: la intensidad profunda del cacao tostado y la suavidad luminosa de la arena cálida. Estos tonos dialogan tanto con atuendos minimalistas de lino blanco como con sastrería invernal estructurada.</p>
<p>Invertir en una cartera de tono neutro de calidad premium asegura que tu pieza permanezca vigente año tras año, adaptándose a cualquier evolución de tu guardarropa.</p>',
                    'en' => '<p>Color is pure emotion. Our signature palette draws inspiration from organic nature: the rich depth of Intense Cocoa and the gentle illumination of Soft Sand.</p>',
                ],
                'meta_title' => [
                    'es' => 'Paleta Neutra: Intense Cocoa y Soft Sand | Leen',
                    'en' => 'Neutral Palette: Intense Cocoa & Soft Sand | Leen',
                ],
                'meta_description' => [
                    'es' => 'Explora cómo combinar carteras en tonos tierra para lograr una estética sofisticada y atemporal.',
                    'en' => 'Learn how to style earthy tone handbags for timeless elegance across all seasons.',
                ],
            ],

            [
                'slug' => 'anatomia-de-un-bolso-estructurado-de-la-piel-al-patron',
                'category_id' => $catArtesania->id,
                'cover_image_path' => 'blog/banner-2.png',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(21),
                'title' => [
                    'es' => 'Anatomía de un Bolso Estructurado: De la Piel al Patrón',
                    'en' => 'Anatomy of a Structured Bag: From Hide to Pattern',
                ],
                'excerpt' => [
                    'es' => 'Un recorrido paso a paso por las más de 40 piezas individuales que componen cada bolso artesanal de nuestra colección.',
                    'en' => 'A step-by-step journey through the 40+ individual components that bring each artisan bag to life.',
                ],
                'content' => [
                    'es' => '<p>Detrás de la aparente simplicidad de una cartera estructurada se esconden horas de cálculo milimétrico. Desde la selección manual del calibre exacto del cuero hasta el tintado de bordes al fuego, cada componente cumple una función vital.</p>
<p>Nuestros maestros marroquineros revisan cada pieza a trasluz, garantizando que el grano natural esté alineado armónicamente en las caras frontal y lateral de la silueta.</p>',
                    'en' => '<p>Behind the effortless elegance of a structured handbag lie hours of meticulous artisan calculations and fire-finished edges.</p>',
                ],
                'meta_title' => [
                    'es' => 'Anatomía y Confección de Bolsos de Cuero | Leen',
                    'en' => 'Anatomy & Craftsmanship of Leather Bags | Leen',
                ],
                'meta_description' => [
                    'es' => 'Conoce cómo se fabrica un bolso de cuero estructurado desde el corte del patrón hasta el acabado final.',
                    'en' => 'Discover how a structured leather bag is crafted from raw pattern cutting to the final edge painting.',
                ],
            ],

            [
                'slug' => 'el-bolso-mini-como-esencial-del-armario-capsula',
                'category_id' => $catEstilo->id,
                'cover_image_path' => 'blog/05.jpeg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(24),
                'title' => [
                    'es' => 'El Bolso Mini como Esencial del Armario Cápsula',
                    'en' => 'The Mini Bag as a Capsule Wardrobe Essential',
                ],
                'excerpt' => [
                    'es' => 'Menos es más: descubre por qué las siluetas compactas son el accesorio definitivo para estilismos modernos y funcionales.',
                    'en' => 'Less is more: discover why compact silhouettes are the definitive accessory for modern functional styling.',
                ],
                'content' => [
                    'es' => '<p>El bolso mini ha dejado de ser una tendencia pasajera para convertirse en un pilar del armario cápsula. Al exigirnos llevar únicamente lo esencial —llaves, teléfono, tarjetero y labial favorito— nos invita a movernos por el mundo con mayor libertad y ligereza.</p>
<p>Combinado con un blazer oversized o un vestido vaporoso, el bolso mini actúa como un punto focal de diseño que aporta sofisticación sin saturar el conjunto.</p>',
                    'en' => '<p>The mini bag has evolved from a trend into a foundational piece of the modern capsule wardrobe, inspiring lightness and freedom.</p>',
                ],
                'meta_title' => [
                    'es' => 'Bolsos Mini en el Armario Cápsula | Leen',
                    'en' => 'Mini Bags in the Capsule Wardrobe | Leen',
                ],
                'meta_description' => [
                    'es' => 'Razones para incorporar un bolso mini en tu armario cápsula y cómo combinarlo para cualquier ocasión.',
                    'en' => 'Why you need a mini leather bag in your capsule wardrobe and how to style it effortlessly.',
                ],
            ],

            [
                'slug' => 'el-tacto-del-lujo-diferencias-entre-grano-natural-y-napa',
                'category_id' => $catArtesania->id,
                'cover_image_path' => 'blog/09.jpeg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(27),
                'title' => [
                    'es' => 'El Tacto del Lujo: Diferencias Entre Grano Natural y Napa',
                    'en' => 'The Touch of Luxury: Natural Grain vs. Nappa Leather',
                ],
                'excerpt' => [
                    'es' => 'Aprende a distinguir los acabados de cuero para elegir la textura que mejor complemente tu estilo de vida y tacto predilecto.',
                    'en' => 'Learn to distinguish leather finishes to choose the texture that best suits your tactile preferences.',
                ],
                'content' => [
                    'es' => '<p>No todos los cueros son iguales. El grano natural conserva los poros, marcas y relieves propios de la piel original, ofreciendo una resistencia superior a rayaduras leves y un aspecto orgánico inconfundible.</p>
<p>Por su parte, la napa destaca por su extrema suavidad, flexibilidad aterciopelada y caída fluida, ideal para siluetas drapeadas y detalles de tacto íntimo.</p>',
                    'en' => '<p>Understanding leather finishes empowers you to select the right sensory experience, from resilient pebble grain to buttery nappa.</p>',
                ],
                'meta_title' => [
                    'es' => 'Tipos de Cuero: Grano Natural vs Napa | Leen',
                    'en' => 'Leather Types: Natural Grain vs Nappa | Leen',
                ],
                'meta_description' => [
                    'es' => 'Guía para diferenciar tipos de cuero y elegir la textura ideal para tus carteras y accesorios.',
                    'en' => 'Expert guide on distinguishing leather finishes and selecting the ideal texture for your handbag.',
                ],
            ],

            [
                'slug' => 'la-magia-de-los-herrajes-dorados-detalles-que-elevan',
                'category_id' => $catEstilo->id,
                'cover_image_path' => 'blog/14.jpeg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(30),
                'title' => [
                    'es' => 'La Magia de los Herrajes Dorados: Detalles que Elevan Cada Silueta',
                    'en' => 'The Allure of Gold Hardware: Accents that Elevate Every Silhouette',
                ],
                'excerpt' => [
                    'es' => 'La joyería del bolso: cómo el brillo sutil de los herrajes Soft Gold complementa la calidez del cuero.',
                    'en' => 'Handbag jewelry: how subtle Soft Gold hardware illuminates the warm richness of artisan leather.',
                ],
                'content' => [
                    'es' => '<p>Los herrajes son la joyería silenciosa de la marroquinería. Un mosquetón bien calibrado, una cremallera de deslizamiento suave y un broche magnético preciso no solo garantizan funcionalidad, sino que aportan destellos de luz que enriquecen el diseño.</p>
<p>Nuestro acabado Soft Gold posee un tono champaña satinado que evita el exceso de brillo, logrando una elegancia atemporal y sobria.</p>',
                    'en' => '<p>Hardware is the subtle jewelry of leather goods, providing smooth tactile interactions and refined luminosity.</p>',
                ],
                'meta_title' => [
                    'es' => 'Herrajes Dorados en Carteras de Lujo | Leen',
                    'en' => 'Gold Hardware in Luxury Handbags | Leen',
                ],
                'meta_description' => [
                    'es' => 'Descubre la importancia de los herrajes en la marroquinería y cómo el acabado Soft Gold eleva cada pieza.',
                    'en' => 'Explore the vital role of premium hardware and Soft Gold accents in luxury handbags.',
                ],
            ],

            [
                'slug' => 'como-organizar-tu-cartera-para-un-dia-de-trabajo-creativo',
                'category_id' => $catEstilo->id,
                'cover_image_path' => 'blog/17.jpg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(33),
                'title' => [
                    'es' => 'Cómo Organizar tu Cartera para un Día de Trabajo Creativo',
                    'en' => 'How to Organize Your Bag for a Creative Workday',
                ],
                'excerpt' => [
                    'es' => 'Estrategias de distribución interna para llevar tu libreta, tecnología y esenciales sin perder ligereza ni orden.',
                    'en' => 'Interior organization strategies to carry your sketchbook, tech, and daily essentials seamlessly.',
                ],
                'content' => [
                    'es' => '<p>Un bolso ordenado es sinónimo de mente despejada. Distribuir los objetos por compartimentos o pequeños estuches de cuero evita el desgaste por fricción interior y te permite acceder a cada herramienta en segundos.</p>
<p>Ubica siempre los objetos más pesados en la base central para mantener el centro de gravedad balanceado y proteger la ergonomía de tu hombro.</p>',
                    'en' => '<p>An organized bag brings peace of mind. Grouping your essentials in leather pouches preserves interior lining and ensures effortless access.</p>',
                ],
                'meta_title' => [
                    'es' => 'Cómo Organizar tu Bolso de Trabajo | Leen',
                    'en' => 'How to Organize Your Work Bag | Leen',
                ],
                'meta_description' => [
                    'es' => 'Consejos de organización interior para carteras de cuero y bolsos de trabajo.',
                    'en' => 'Practical interior organization tips for work handbags and leather totes.',
                ],
            ],

            [
                'slug' => 'la-melodia-del-color-miel-el-simbolismo-detras-de-leen',
                'category_id' => $catPanal->id,
                'cover_image_path' => 'blog/18.jpg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(36),
                'title' => [
                    'es' => 'La Melodía del Color Miel: El Simbolismo Detrás de Leen',
                    'en' => 'The Honey Melody: The Symbolism Behind Leen',
                ],
                'excerpt' => [
                    'es' => 'La miel como metáfora de perseverancia, afecto y calidez en cada puntada de nuestra colección.',
                    'en' => 'Honey as a metaphor for perseverance, warmth, and conscious craftsmanship in every stitch.',
                ],
                'content' => [
                    'es' => '<p>«Sweeter than honey» no es solo un lema; es la declaración de intenciones que inspira nuestra marca. La miel representa el trabajo incansable, la alquimia de transformar recursos naturales en algo noble y la dulzura que deseamos infundir en la vida cotidiana.</p>
<p>Vestir con miel significa rodearse de objetos que despiertan alegría, hechos con dedicación sincera y diseñados para perdurar.</p>',
                    'en' => '<p>“Sweeter than honey” is our guiding ethos. Honey represents communal dedication, natural transformation, and warm everyday joy.</p>',
                ],
                'meta_title' => [
                    'es' => 'El Simbolismo de la Miel en Leen | Leen Journal',
                    'en' => 'The Symbolism of Honey at Leen | Leen Journal',
                ],
                'meta_description' => [
                    'es' => 'Conoce el significado y la filosofía detrás del lema y la estética inspirada en la miel de Leen.',
                    'en' => 'Discover the story and emotional inspiration behind Leen’s honey-inspired craftsmanship.',
                ],
            ],

            [
                'slug' => 'guia-para-eliminar-manchas-accidentales-en-cuero-legitimo',
                'category_id' => $catCuidado->id,
                'cover_image_path' => 'blog/banner-8.jpg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(39),
                'title' => [
                    'es' => 'Guía de Emergencia: Cómo Tratar Manchas Accidentales en Cuero Legítimo',
                    'en' => 'Emergency Guide: Treating Accidental Stains on Genuine Leather',
                ],
                'excerpt' => [
                    'es' => 'Qué hacer (y qué evitar rotundamente) ante manchas de café, tinta o grasa en tu cartera favorita.',
                    'en' => 'What to do (and what strictly to avoid) when dealing with coffee, ink, or oil stains on your leather bag.',
                ],
                'content' => [
                    'es' => '<p>Los accidentes ocurren, pero actuar con calma y el método adecuado puede salvar tu pieza de cuero. La regla primordial es la absorción inmediata sin frotar con fuerza.</p>
<p>Para manchas líquidas, presiona suavemente un papel absorbente seco. Jamás apliques alcohol, toallitas desmaquillantes con químicos abrasivos ni vinagre sobre el cuero, ya que despojan el tinte original de la piel.</p>',
                    'en' => '<p>Accidents happen, but prompt, gentle action can rescue your leather bag. Always blot without rubbing, and avoid alcohol or harsh wipes.</p>',
                ],
                'meta_title' => [
                    'es' => 'Cómo Limpiar Manchas en Cuero | Leen',
                    'en' => 'How to Clean Stains on Leather | Leen',
                ],
                'meta_description' => [
                    'es' => 'Aprende los métodos seguros para eliminar manchas en carteras de cuero genuino sin dañar el acabado.',
                    'en' => 'Learn safe and effective methods to remove stains from genuine leather bags.',
                ],
            ],

            [
                'slug' => 'de-dia-a-la-noche-transformando-tu-look-con-un-solo-accesorio',
                'category_id' => $catEstilo->id,
                'cover_image_path' => 'blog/banner-9.jpg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(42),
                'title' => [
                    'es' => 'De Día a la Noche: Transformando tu Look con un Solo Accesorio',
                    'en' => 'Day to Night: Transforming Your Look with a Single Accessory',
                ],
                'excerpt' => [
                    'es' => 'Consejos de estilismo para pasar de una reunión de trabajo a una cena especial cambiando solo el porte de tu cartera.',
                    'en' => 'Styling tips to transition seamlessly from boardroom meetings to dinner soirées with one versatile handbag.',
                ],
                'content' => [
                    'es' => '<p>La verdadera versatilidad de una cartera se pone a prueba en las jornadas continuas. Desmontar la correa larga para llevarla como bolso de mano (clutch) o ajustar la caída de la cadena dorada transforma al instante la formalidad de tu conjunto.</p>
<p>Nuestras piezas están diseñadas con siluetas modulares que se integran con naturalidad en cualquier momento del día.</p>',
                    'en' => '<p>True versatility shines when a handbag transitions effortlessly between day and evening by detaching shoulder straps or adjusting gold chains.</p>',
                ],
                'meta_title' => [
                    'es' => 'Looks de Día a Noche con Carteras de Cuero | Leen',
                    'en' => 'Day-to-Night Handbag Styling | Leen',
                ],
                'meta_description' => [
                    'es' => 'Descubre cómo adaptar tus accesorios para transiciones fluidas de día a noche con estilo.',
                    'en' => 'Styling guide on transitioning leather accessories from daytime chic to evening elegance.',
                ],
            ],

            [
                'slug' => 'la-filosofia-wabi-sabi-en-la-patina-del-cuero',
                'category_id' => $catCuidado->id,
                'cover_image_path' => 'blog/banner-2.png',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(45),
                'title' => [
                    'es' => 'La Filosofía Wabi-Sabi en la Pátina del Cuero: La Belleza de lo Imperfecto',
                    'en' => 'Wabi-Sabi Philosophy in Leather Patina: The Beauty of Imperfection',
                ],
                'excerpt' => [
                    'es' => 'Por qué las pequeñas marcas y el oscurecimiento natural del cuero hacen que tu bolso sea aún más valioso y personal.',
                    'en' => 'Why subtle marks and organic patina make your leather bag even more cherished and distinctively yours.',
                ],
                'content' => [
                    'es' => '<p>En la tradición japonesa, el concepto de Wabi-Sabi invita a celebrar la belleza de las cosas imperfectas, mudables e incompletas. En la marroquinería fina, esto se traduce en abrazar la evolución del cuero.</p>
<p>Cada cambio de tono, cada pliegue suave y cada marca de uso es un testimonio de las experiencias vividas junto a tu pieza, alejándonos de la frialdad sintética de los materiales plásticos.</p>',
                    'en' => '<p>The Japanese philosophy of Wabi-Sabi honors the grace of natural aging, making your leather handbag a living diary of your adventures.</p>',
                ],
                'meta_title' => [
                    'es' => 'Wabi-Sabi y la Pátina del Cuero | Leen Journal',
                    'en' => 'Wabi-Sabi and Leather Patina | Leen Journal',
                ],
                'meta_description' => [
                    'es' => 'Explora cómo la filosofía Wabi-Sabi nos enseña a valorar la pátina y el envejecimiento noble del cuero auténtico.',
                    'en' => 'Discover the beauty of organic aging and patina in authentic handcrafted leather.',
                ],
            ],

            [
                'slug' => 'el-viaje-de-una-pieza-leen-desde-el-boceto-hasta-tus-manos',
                'category_id' => $catArtesania->id,
                'cover_image_path' => 'blog/05.jpeg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(48),
                'title' => [
                    'es' => 'El Viaje de una Pieza Leen: Desde el Boceto hasta tus Manos',
                    'en' => 'The Journey of a Leen Piece: From Initial Sketch to Your Hands',
                ],
                'excerpt' => [
                    'es' => 'El camino creativo detrás de cada modelo: prototipado, pruebas de resistencia y el empaque perfumado que llega a tu puerta.',
                    'en' => 'The creative path behind each model: prototyping, stress testing, and the fragrant package arriving at your door.',
                ],
                'content' => [
                    'es' => '<p>Antes de que una cartera llegue a la tienda, pasa por meses de iteración. Dibujamos decenas de siluetas, probamos la caída de las asas en diferentes alturas corporales y sometemos los broches a miles de aperturas continuas.</p>
<p>Cuando finalmente desempacas tu caja Leen envuelta en papel de seda, estás recibiendo el fruto de un proceso donde nada fue dejado al azar.</p>',
                    'en' => '<p>Every Leen bag undergoes rigorous design iterations and ergonomics testing before reaching your hands in our signature packaging.</p>',
                ],
                'meta_title' => [
                    'es' => 'El Proceso Creativo en Leen | Leen Journal',
                    'en' => 'The Creative Process at Leen | Leen Journal',
                ],
                'meta_description' => [
                    'es' => 'Conoce la travesía completa de diseño y producción artesanal de las carteras Leen.',
                    'en' => 'Follow the journey of a handcrafted handbag from the initial sketchbook to doorstep delivery.',
                ],
            ],

            [
                'slug' => 'maxi-bags-vs-mini-bags-encuentra-tu-proporcion-ideal',
                'category_id' => $catEstilo->id,
                'cover_image_path' => 'blog/09.jpeg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(51),
                'title' => [
                    'es' => 'Maxi Bags vs. Mini Bags: Encuentra tu Proporción Ideal',
                    'en' => 'Maxi Bags vs. Mini Bags: Finding Your Ideal Scale',
                ],
                'excerpt' => [
                    'es' => 'Analizamos los pros y contras de cada escala de bolso según tu estatura, rutina y necesidades de almacenaje.',
                    'en' => 'Comparing the benefits of each handbag scale based on your height, daily routine, and carrying needs.',
                ],
                'content' => [
                    'es' => '<p>El tamaño de tu bolso no solo responde a lo que necesitas cargar, sino a la proporción visual con tu silueta. Un Maxi Bag aporta una presencia editorial imponente, ideal para jornadas extensas y viajes de fin de semana.</p>
<p>Los tamaños intermedios (Medium) y mini equilibran la figura, permitiendo que las prendas de vestir sigan siendo las protagonistas del look.</p>',
                    'en' => '<p>Finding the right handbag proportion balances comfort, carrying capacity, and visual silhouette harmony.</p>',
                ],
                'meta_title' => [
                    'es' => 'Maxi Bags vs Mini Bags: Guía de Tamaños | Leen',
                    'en' => 'Maxi Bags vs Mini Bags: Size Guide | Leen',
                ],
                'meta_description' => [
                    'es' => 'Descubre cuál es el tamaño de cartera perfecto para tu estilo de vida y proporciones corporales.',
                    'en' => 'Find the perfect handbag scale for your daily lifestyle and wardrobe aesthetics.',
                ],
            ],

            [
                'slug' => 'por-que-el-cuero-curtido-vegetal-es-el-futuro-del-lujo-sostenible',
                'category_id' => $catArtesania->id,
                'cover_image_path' => 'blog/14.jpeg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(54),
                'title' => [
                    'es' => 'Por Qué el Cuero Sostenible es el Futuro del Lujo Consciente',
                    'en' => 'Why Sustainable Leathercraft is the Future of Conscious Luxury',
                ],
                'excerpt' => [
                    'es' => 'La diferencia entre materiales sintéticos desechables y cuero de procedencia responsable que perdura por décadas.',
                    'en' => 'The difference between disposable synthetic alternatives and responsibly sourced leather crafted to last decades.',
                ],
                'content' => [
                    'es' => '<p>En la búsqueda de la sostenibilidad, la longevidad es el factor más determinante. A diferencia de los materiales plásticos sintéticos que se desmoronan en pocos años, el cuero auténtico bien tratado tiene una vida útil de décadas.</p>
<p>Elegir piezas duraderas reduce el consumo innecesario y apoya a talleres artesanales comprometidos con procesos limpios y éticos.</p>',
                    'en' => '<p>Longevity is the true pillar of sustainability. Responsibly crafted leather outlasts synthetic alternatives by decades, reducing waste.</p>',
                ],
                'meta_title' => [
                    'es' => 'Lujo Consciente y Cuero Sostenible | Leen',
                    'en' => 'Conscious Luxury & Sustainable Leather | Leen',
                ],
                'meta_description' => [
                    'es' => 'Explora por qué la durabilidad y la marroquinería ética son la respuesta al fast fashion.',
                    'en' => 'Learn why enduring craftsmanship and responsible sourcing define the future of luxury.',
                ],
            ],

            [
                'slug' => 'el-ritual-del-guardapolvo-como-almacenar-bolsos-en-cambio-de-temporada',
                'category_id' => $catCuidado->id,
                'cover_image_path' => 'blog/17.jpg',
                'status' => PostStatusEnum::Published,
                'published_at' => now()->subDays(57),
                'title' => [
                    'es' => 'El Ritual del Guardapolvo: Cómo Almacenar Bolsos en Cambio de Temporada',
                    'en' => 'The Dust Bag Ritual: Seasonal Handbag Storage Guide',
                ],
                'excerpt' => [
                    'es' => 'Pasos esenciales para preparar tus carteras antes de guardarlas por varios meses y encontrarlas impecables al sacarlas.',
                    'en' => 'Essential steps to prepare your leather bags before seasonal storage to ensure they remain pristine.',
                ],
                'content' => [
                    'es' => '<p>Cuando llega el momento de rotar tu armario, dedicar unos minutos al cuidado de tus bolsos antes de guardarlos garantiza que conserven su lustre original. Límpialos con un paño seco, hidrata ligeramente los bordes y evita colocarlos aplastados unos sobre otros.</p>
<p>Utiliza siempre bolsas de algodón transpirable (nunca bolsas plásticas herméticas) para permitir que la piel respire y no acumule humedad.</p>',
                    'en' => '<p>Seasonal handbag rotation deserves a mindful ritual. Storing your pieces in breathable cotton dust bags keeps them pristine for years.</p>',
                ],
                'meta_title' => [
                    'es' => 'Cómo Guardar Bolsos en Cambio de Temporada | Leen',
                    'en' => 'Seasonal Handbag Storage Tips | Leen',
                ],
                'meta_description' => [
                    'es' => 'Guía paso a paso para almacenar y proteger tus carteras de cuero entre temporadas.',
                    'en' => 'Step-by-step guide to seasonal leather handbag storage and protection.',
                ],
            ],
        ];

        foreach ($posts as $postData) {
            Post::query()->updateOrCreate(
                ['slug' => $postData['slug']],
                [
                    'author_id' => $author->id,
                    'post_category_id' => $postData['category_id'],
                    'cover_image_path' => $postData['cover_image_path'],
                    'status' => $postData['status'],
                    'published_at' => $postData['published_at'],
                    'title' => $postData['title'],
                    'excerpt' => $postData['excerpt'],
                    'content' => $postData['content'],
                    'meta_title' => $postData['meta_title'],
                    'meta_description' => $postData['meta_description'],
                ]
            );
        }
    }

    private function seedBlogImages(): void
    {
        Storage::disk('public')->makeDirectory('blog');

        $availableSources = [
            '05.jpeg' => public_path('images/about/05.jpeg'),
            '09.jpeg' => public_path('images/about/09.jpeg'),
            '14.jpeg' => public_path('images/about/14.jpeg'),
            '17.jpg' => public_path('images/about/17.jpg'),
            '18.jpg' => public_path('images/about/18.jpg'),
            'banner-2.png' => public_path('images/about/banner-2.png'),
            'banner-8.jpg' => public_path('images/about/banner-8.jpg'),
            'banner-9.jpg' => public_path('images/about/banner-9.jpg'),
        ];

        foreach ($availableSources as $filename => $sourcePath) {
            if (File::exists($sourcePath)) {
                $destination = Storage::disk('public')->path('blog/'.$filename);
                if (! File::exists($destination)) {
                    File::copy($sourcePath, $destination);
                }
            }
        }
    }
}
