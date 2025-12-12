-- Add new columns to support rich product data
alter table public.bdjr_products add column if not exists vendor text;
alter table public.bdjr_products add column if not exists long_description text;
alter table public.bdjr_products add column if not exists compare_price numeric;
alter table public.bdjr_products add column if not exists features jsonb;
alter table public.bdjr_products add column if not exists benefits jsonb;
alter table public.bdjr_products add column if not exists images jsonb;

-- Clear existing products to avoid duplicates (Optional, remove if you want to keep old ones)
truncate table public.bdjr_products cascade;

-- Insert new products
insert into public.bdjr_products (name, vendor, description, long_description, price, compare_price, images, category, features, benefits, image_url, stock) values
(
    'Software de Finanzas personales',
    'BDJR',
    'Disponible en Android (apk), Web y Windows (exe).',
    'Toma el control total de tu economía con nuestra suite de finanzas personales. Registra gastos, establece presupuestos inteligentes y visualiza tu crecimiento patrimonial con gráficos intuitivos. Ideal para alcanzar la libertad financiera.',
    50000,
    100000,
    '["assets/images/products/FinanzApp1.png", "assets/images/products/FinanzApp2.png", "assets/images/products/FinanzApp4.png", "assets/images/products/FinanzApp3.png"]',
    'Software',
    '["Seguimiento de Gastos", "Presupuestos Personalizados", "Reportes Mensuales", "Sincronización Bancaria"]',
    '["Ahorra más dinero mes a mes", "Control total de tus finanzas", "Planificación financiera sin estrés"]',
    'assets/images/products/FinanzApp1.png',
    100
),
(
    'Software para administrar negocios',
    'BDJR',
    'Disponible en Android (apk), Web y Windows (exe).',
    'La solución integral para emprendedores y PyMEs. Centraliza tu inventario, facturación y relación con clientes en una sola plataforma. Simplifica la gestión operativa y enfócate en hacer crecer tu empresa.',
    50000,
    100000,
    '["assets/images/products/ComingSoon.png"]',
    'Software',
    '["Control de Inventario", "Facturación Electrónica", "Gestión de Clientes (CRM)", "Reportes de Ventas"]',
    '["Optimiza tus procesos operativos", "Aumenta tus ventas con datos claros", "Toma de decisiones informada"]',
    'assets/images/products/ComingSoon.png',
    100
),
(
    'Software para planificar rutinas semanales',
    'BDJR',
    'Disponible en Web',
    'Maximiza tu productividad organizando tu semana con precisión. Diseña rutinas equilibradas, establece recordatorios y visualiza tu progreso. La herramienta perfecta para mantener el balance entre vida laboral y personal.',
    50000,
    100000,
    '["assets/images/products/PlanningHub1.png", "assets/images/products/PlanningHub2.png", "assets/images/products/PlanningHub3.png"]',
    'Software',
    '["Calendario Interactivo", "Recordatorios Automáticos", "Metas Semanales", "Plantillas de Rutinas"]',
    '["Mejora tu productividad semanal", "Organización eficiente del tiempo", "Construcción de hábitos saludables"]',
    'assets/images/products/PlanningHub1.png',
    100
),
(
    'Software para planificar rutinas diarias',
    'BDJR',
    'Disponible en Android (apk) y Windows (exe).',
    'Domina tu día a día con nuestra app de planificación diaria. Prioriza tareas, bloquea tiempos de enfoque y construye hábitos sólidos. Transforma tu caos diario en una estructura productiva y satisfactoria.',
    50000,
    100000,
    '["assets/images/products/RutinApp1.png", "assets/images/products/RutinApp2.png"]',
    'Software',
    '["Lista de Tareas (To-Do)", "Bloques de Tiempo", "Seguimiento de Hábitos", "Modo Enfoque"]',
    '["Reduce el estrés diario", "Cumple tus objetivos diarios", "Mejor gestión del tiempo"]',
    'assets/images/products/RutinApp1.png',
    100
),
(
    'Software de ruleta aleatoria',
    'BDJR',
    'Disponible en Android (apk) y Windows (exe).',
    'Añade diversión y aleatoriedad a tus decisiones. Crea ruletas personalizadas para sorteos, juegos en grupo o toma de decisiones rápidas. Interfaz animada y resultados 100% aleatorios garantizados.',
    50000,
    100000,
    '["assets/images/products/RuletApp1.png", "assets/images/products/RuletApp2.png"]',
    'Software',
    '["Opciones Personalizables", "Animaciones Fluidas", "Guardado de Listas", "Resultados Imparciales"]',
    '["Toma decisiones rápidas", "Dinámicas divertidas para grupos", "Sorteos justos y transparentes"]',
    'assets/images/products/RuletApp1.png',
    100
);
