-- Admin - password123
INSERT INTO users (name, email, password_hash, role)
VALUES ("Admin", "paydesk_admin@local.test", "$2y$12$ZQXEIDMRTNEViMSgLrTXlO5L.b30xu91llCF1HxLdbwQlvponNtlO", "Admin");
INSERT INTO users (name, email, password_hash, role)
-- Customer - password456
VALUES ("Customer", "paydesk_customer@local.test", "$2y$12$Mn/A6boLDPh83MPw8iI1M.1jlJl.W6mLvDVOHSvOJflS32MWK.FwW", "Customer");

INSERT INTO products (name, description, price, stock, is_active)
VALUES ("Wireless Mouse", "Ergonomic wireless mouse with adjustable DPI, a comfortable grip, and reliable 2.4GHz connectivity for everyday computing.", 25.00, 50, 1);
INSERT INTO products (name, description, price, stock, is_active)
VALUES ("Mechanical Keyboard", "Compact mechanical keyboard with tactile switches, durable keycaps, and a responsive typing experience for work and gaming.", 75.00, 30, 1);
INSERT INTO products (name, description, price, stock, is_active)
VALUES ("USB-C Hub", "Multiport USB-C hub featuring USB-A ports, HDMI output, and USB-C pass-through charging for compatible laptops and tablets.", 45.00, 25, 1);
INSERT INTO products (name, description, price, stock, is_active)
VALUES ("Adjustable Laptop Stand", "Lightweight aluminum laptop stand with adjustable height and angle to improve desk ergonomics and airflow.", 35.00, 40, 1);
INSERT INTO products (name, description, price, stock, is_active)
VALUES ("Full HD Webcam", "1080p USB webcam with automatic light correction and a built-in microphone for video meetings and online classes.", 55.00, 20, 1);
