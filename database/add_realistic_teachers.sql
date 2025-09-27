-- Add Realistic Teachers for Philippine SHS Departments
-- This script adds 4-6 teachers per department for proper peer evaluation testing
-- STEM Department Teachers (Academic Track)
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'maria.santos',
        'Maria',
        'Santos',
        'maria.santos@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'STEM'
    ),
    (
        'juan.delacruz',
        'Juan',
        'Dela Cruz',
        'juan.delacruz@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'STEM'
    ),
    (
        'catherine.reyes',
        'Catherine',
        'Reyes',
        'catherine.reyes@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'STEM'
    ),
    (
        'michael.garcia',
        'Michael',
        'Garcia',
        'michael.garcia@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'STEM'
    ),
    (
        'ana.fernandez',
        'Ana',
        'Fernandez',
        'ana.fernandez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'STEM'
    );

-- ABM (Accountancy, Business and Management) Department Teachers
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'roberto.villanueva',
        'Roberto',
        'Villanueva',
        'roberto.villanueva@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ABM'
    ),
    (
        'carmen.torres',
        'Carmen',
        'Torres',
        'carmen.torres@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ABM'
    ),
    (
        'ricardo.morales',
        'Ricardo',
        'Morales',
        'ricardo.morales@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ABM'
    ),
    (
        'elena.castillo',
        'Elena',
        'Castillo',
        'elena.castillo@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ABM'
    ),
    (
        'daniel.herrera',
        'Daniel',
        'Herrera',
        'daniel.herrera@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ABM'
    );

-- HUMSS (Humanities and Social Sciences) Department Teachers
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'sofia.aquino',
        'Sofia',
        'Aquino',
        'sofia.aquino@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'HUMSS'
    ),
    (
        'gabriel.mendoza',
        'Gabriel',
        'Mendoza',
        'gabriel.mendoza@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'HUMSS'
    ),
    (
        'isabella.navarro',
        'Isabella',
        'Navarro',
        'isabella.navarro@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'HUMSS'
    ),
    (
        'carlos.jimenez',
        'Carlos',
        'Jimenez',
        'carlos.jimenez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'HUMSS'
    ),
    (
        'patricia.ramos',
        'Patricia',
        'Ramos',
        'patricia.ramos@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'HUMSS'
    ),
    (
        'luis.gutierrez',
        'Luis',
        'Gutierrez',
        'luis.gutierrez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'HUMSS'
    );

-- GAS (General Academic Strand) Department Teachers
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'cristina.alvarez',
        'Cristina',
        'Alvarez',
        'cristina.alvarez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'GAS'
    ),
    (
        'fernando.rojas',
        'Fernando',
        'Rojas',
        'fernando.rojas@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'GAS'
    ),
    (
        'angela.vargas',
        'Angela',
        'Vargas',
        'angela.vargas@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'GAS'
    ),
    (
        'marco.ortega',
        'Marco',
        'Ortega',
        'marco.ortega@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'GAS'
    ),
    (
        'rosario.silva',
        'Rosario',
        'Silva',
        'rosario.silva@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'GAS'
    );

-- ICT (Information and Communications Technology) Department Teachers
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'paolo.cruz',
        'Paolo',
        'Cruz',
        'paolo.cruz@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ICT'
    ),
    (
        'melissa.perez',
        'Melissa',
        'Perez',
        'melissa.perez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ICT'
    ),
    (
        'ryan.flores',
        'Ryan',
        'Flores',
        'ryan.flores@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ICT'
    ),
    (
        'stephanie.gonzalez',
        'Stephanie',
        'Gonzalez',
        'stephanie.gonzalez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ICT'
    ),
    (
        'adrian.martinez',
        'Adrian',
        'Martinez',
        'adrian.martinez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ICT'
    );

-- Core Department Teachers (Core Subjects for all strands)
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'grace.domingo',
        'Grace',
        'Domingo',
        'grace.domingo@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'Core'
    ),
    (
        'benjamin.castro',
        'Benjamin',
        'Castro',
        'benjamin.castro@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'Core'
    ),
    (
        'victoria.aguilar',
        'Victoria',
        'Aguilar',
        'victoria.aguilar@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'Core'
    ),
    (
        'antonio.sandoval',
        'Antonio',
        'Sandoval',
        'antonio.sandoval@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'Core'
    ),
    (
        'diana.medina',
        'Diana',
        'Medina',
        'diana.medina@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'Core'
    ),
    (
        'jorge.romero',
        'Jorge',
        'Romero',
        'jorge.romero@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'Core'
    );

-- Additional Teachers for More Realistic Testing
-- Adding more teachers to each department for better peer evaluation
-- Additional STEM Teachers
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'david.torres',
        'David',
        'Torres',
        'david.torres@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'STEM'
    ),
    (
        'sarah.martinez',
        'Sarah',
        'Martinez',
        'sarah.martinez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'STEM'
    ),
    (
        'carlos.lopez',
        'Carlos',
        'Lopez',
        'carlos.lopez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'STEM'
    );

-- Additional ABM Teachers
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'monica.santos',
        'Monica',
        'Santos',
        'monica.santos@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ABM'
    ),
    (
        'francisco.cruz',
        'Francisco',
        'Cruz',
        'francisco.cruz@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ABM'
    ),
    (
        'laura.rivera',
        'Laura',
        'Rivera',
        'laura.rivera@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ABM'
    );

-- Additional HUMSS Teachers
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'diego.ramos',
        'Diego',
        'Ramos',
        'diego.ramos@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'HUMSS'
    ),
    (
        'valentina.flores',
        'Valentina',
        'Flores',
        'valentina.flores@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'HUMSS'
    ),
    (
        'emilio.perez',
        'Emilio',
        'Perez',
        'emilio.perez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'HUMSS'
    );

-- Additional GAS Teachers
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'natalia.garcia',
        'Natalia',
        'Garcia',
        'natalia.garcia@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'GAS'
    ),
    (
        'miguel.rodriguez',
        'Miguel',
        'Rodriguez',
        'miguel.rodriguez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'GAS'
    ),
    (
        'camila.herrera',
        'Camila',
        'Herrera',
        'camila.herrera@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'GAS'
    );

-- Additional ICT Teachers (Making sure it's ICT, not IT)
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'alexander.diaz',
        'Alexander',
        'Diaz',
        'alexander.diaz@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ICT'
    ),
    (
        'jessica.moreno',
        'Jessica',
        'Moreno',
        'jessica.moreno@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ICT'
    ),
    (
        'leonardo.ruiz',
        'Leonardo',
        'Ruiz',
        'leonardo.ruiz@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ICT'
    ),
    (
        'isabelle.vargas',
        'Isabelle',
        'Vargas',
        'isabelle.vargas@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ICT'
    ),
    (
        'ramon.castillo',
        'Ramon',
        'Castillo',
        'ramon.castillo@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'ICT'
    );

-- Additional Core Teachers
INSERT INTO
    users (
        username,
        firstname,
        lastname,
        email,
        password,
        user_type,
        department
    )
VALUES
    (
        'beatriz.jimenez',
        'Beatriz',
        'Jimenez',
        'beatriz.jimenez@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'Core'
    ),
    (
        'ignacio.mendoza',
        'Ignacio',
        'Mendoza',
        'ignacio.mendoza@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'Core'
    ),
    (
        'alicia.navarro',
        'Alicia',
        'Navarro',
        'alicia.navarro@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'Core'
    ),
    (
        'sergio.ortega',
        'Sergio',
        'Ortega',
        'sergio.ortega@school.edu',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'teacher',
        'Core'
    );

-- Fix existing teacher with IT department to ICT
UPDATE users
SET
    department = 'ICT'
WHERE
    department = 'IT';

-- Summary of All Teachers Added:
-- STEM: 8 teachers (5 original + 3 additional)
-- ABM: 8 teachers (5 original + 3 additional)  
-- HUMSS: 9 teachers (6 original + 3 additional)
-- GAS: 8 teachers (5 original + 3 additional)
-- ICT: 10 teachers (5 original + 5 additional) - MOST TEACHERS FOR ICT
-- Core: 10 teachers (6 original + 4 additional)
-- Total New Teachers Added: 53 teachers
-- This provides excellent peer evaluation scenarios with 8-10 teachers per department