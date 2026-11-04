-- ═══════════════════════════════════════════════════════════════
-- Saizul Amin Portfolio — Production Seed Data
-- Run AFTER schema.sql: mysql -u user -p dbname < seed.sql
-- ═══════════════════════════════════════════════════════════════

SET NAMES utf8mb4;

-- ── Default Settings ──────────────────────────────────────────
INSERT INTO `settings` (`key_name`, `value`, `group_name`) VALUES
('site_name',             'Saizul Amin',                                            'general'),
('site_tagline',          'AI Engineer & Full-Stack Developer',                     'general'),
('contact_email',         'saizulamin@gmail.com',                                   'general'),
('social_github',         'https://github.com/saizulamin',                          'social'),
('social_linkedin',       'https://linkedin.com/in/saizulamin',                     'social'),
('social_twitter',        'https://twitter.com/saizulamin',                         'social'),
('seo_default_title',     'Saizul Amin — AI Engineer & Full-Stack Developer',       'seo'),
('seo_default_description','Building intelligent AI systems and scalable full-stack applications. Python, PyTorch, LangChain, PHP, React.', 'seo'),
('seo_og_image',          '/assets/images/og-default.jpg',                          'seo'),
('robots_rules',          '',                                                        'general')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- ── Skills ────────────────────────────────────────────────────
INSERT INTO `skills` (`name`, `category`, `proficiency`, `order_col`) VALUES
('Python',        'AI & ML',         95,  1),
('PyTorch',       'AI & ML',         88,  2),
('LangChain',     'AI & ML',         90,  3),
('OpenAI API',    'AI & ML',         92,  4),
('HuggingFace',   'AI & ML',         85,  5),
('scikit-learn',  'AI & ML',         82,  6),
('FastAPI',       'Backend',         88,  7),
('PHP 8',         'Backend',         90,  8),
('MySQL',         'Backend',         85,  9),
('Redis',         'Backend',         75, 10),
('Docker',        'DevOps',          78, 11),
('AWS',           'DevOps',          72, 12),
('CI/CD',         'DevOps',          74, 13),
('React',         'Frontend',        82, 14),
('TypeScript',    'Frontend',        78, 15),
('Next.js',       'Frontend',        75, 16),
('TailwindCSS',   'Frontend',        90, 17),
('Three.js',      'Frontend',        80, 18),
('PostgreSQL',    'Backend',         78, 19),
('Kafka',         'Backend',         65, 20);

-- ── Experience ────────────────────────────────────────────────
INSERT INTO `experience` (`company`, `role`, `description`, `start_date`, `end_date`, `is_current`, `type`, `order_col`) VALUES
('Independent / Freelance', 'AI Engineer & Full-Stack Developer',
 'Building production AI systems: RAG pipelines, LLM-powered applications, custom fine-tuned models. Clients across fintech, e-commerce, and SaaS.',
 '2022-01-01', NULL, 1, 'work', 1),

('Tech Startup (Confidential)', 'Senior Full-Stack Developer',
 'Led architecture and development of a multi-tenant SaaS platform serving 10k+ users. PHP + React + MySQL stack. Implemented real-time features via WebSockets.',
 '2020-06-01', '2021-12-31', 0, 'work', 2),

('Digital Agency', 'Full-Stack Developer',
 'Developed 20+ client websites and web applications. Specialised in performance optimisation and CMS integrations.',
 '2019-01-01', '2020-05-31', 0, 'work', 3),

('University', 'Bachelor of Computer Science',
 'Specialisation in Artificial Intelligence and Software Engineering. Final year project: NLP-based document classification system.',
 '2015-09-01', '2019-06-30', 0, 'education', 4);

-- ── Projects ──────────────────────────────────────────────────
INSERT INTO `projects` (`title`, `slug`, `description`, `tech_stack`, `category`, `featured`, `order_col`, `status`) VALUES
('RAG Document Intelligence', 'rag-document-intelligence',
 'Production-grade Retrieval Augmented Generation system that ingests enterprise documents and enables natural-language Q&A with source citations. Handles 100k+ page corpora.',
 'Python, LangChain, OpenAI, ChromaDB, FastAPI, React', 'ai', 1, 1, 'published'),

('AI Code Review Agent', 'ai-code-review-agent',
 'Autonomous code review pipeline using GPT-4 and AST analysis. Integrates with GitHub PRs, flags security vulnerabilities, suggests refactors, and generates test coverage reports.',
 'Python, OpenAI, AST, GitHub API, FastAPI', 'ai', 1, 2, 'published'),

('LLM Fine-tuning Platform', 'llm-finetuning-platform',
 'Self-hosted platform for fine-tuning open-source LLMs on custom datasets. Supports LoRA/QLoRA, tracks experiments, and exports to GGUF for local inference.',
 'Python, PyTorch, HuggingFace, LoRA, FastAPI, Docker', 'ai', 0, 3, 'published'),

('Conversational AI Chatbot', 'conversational-ai-chatbot',
 'Multi-turn conversational AI with long-term memory, tool use, and function calling. Deployed as a customer support assistant handling 1000+ queries/day.',
 'Python, LangChain, Redis, OpenAI, WebSockets', 'ai', 0, 4, 'published'),

('Neural Sentiment Analyser', 'neural-sentiment-analyser',
 'BERT-based sentiment analysis system fine-tuned on domain-specific data. REST API serving real-time predictions with 94% accuracy on product review classification.',
 'Python, PyTorch, HuggingFace, FastAPI, Docker', 'ai', 0, 5, 'published'),

('Personal Brand Platform', 'personal-brand-platform',
 'This website. Custom PHP MVC framework, Three.js 3D environments, GSAP animations, SEO system, CMS with page builder. Built for performance and Lighthouse 95+.',
 'PHP 8.3, Three.js, GSAP, MySQL, TailwindCSS', 'portfolio', 1, 6, 'published'),

('SaaS Billing Dashboard', 'saas-billing-dashboard',
 'Real-time billing and subscription management dashboard. Stripe integration, usage-based pricing, automated invoicing, and analytics charts.',
 'React, TypeScript, Node.js, Stripe, PostgreSQL', 'portfolio', 0, 7, 'published'),

('E-commerce Performance Rebuild', 'ecommerce-performance-rebuild',
 'Rebuilt a slow WooCommerce store into a headless Next.js + custom PHP API stack. Page load reduced from 8s to 0.9s. Revenue increased 34% post-launch.',
 'Next.js, PHP, MySQL, TailwindCSS, Redis', 'portfolio', 0, 8, 'published');

-- ── Blog Posts ────────────────────────────────────────────────
INSERT INTO `posts` (`title`, `slug`, `excerpt`, `body`, `status`, `published_at`) VALUES
('Building Production RAG Systems: Lessons from the Field',
 'building-production-rag-systems',
 'After deploying RAG pipelines to production across multiple clients, here are the hard lessons about chunking strategies, retrieval quality, and avoiding the common pitfalls.',
 '<p>Retrieval Augmented Generation has moved from research novelty to production workhorse in under two years. After building and deploying RAG systems for enterprise clients across fintech, legal, and e-commerce, I have learned that the gap between a demo RAG system and a production-grade one is enormous.</p><h2>The Chunking Problem</h2><p>Most tutorials show you how to chunk documents by fixed character count. In production, this destroys context. A paragraph split mid-sentence produces embeddings that retrieve irrelevant results. I now use semantic chunking — splitting on paragraph boundaries and respecting document structure — combined with a sliding window overlap of 15%.</p><h2>Retrieval Quality Is Everything</h2><p>Your LLM is only as good as what you retrieve. I have moved from pure vector search to a hybrid approach: BM25 for keyword precision combined with cosine similarity for semantic relevance. The reranking step using a cross-encoder dramatically improves the final result quality.</p><p>More on this in future posts.</p>',
 'published', '2024-11-15 09:00:00'),

('Why I Rebuilt My Stack Around LangChain',
 'why-i-rebuilt-around-langchain',
 'LangChain gets a lot of criticism for complexity. Here is why I think the abstraction is worth it, and where I draw the line.',
 '<p>The LangChain criticism is fair: it is complex, the abstractions leak, and debugging chains is painful. But after building several production AI systems with and without it, I keep coming back.</p><h2>What It Gets Right</h2><p>The composability model is genuinely good. Building a RAG chain, wrapping it in a conversational interface with memory, adding tool use — this is 50 lines with LangChain vs 400 lines of plumbing without it. The abstractions are leaky but the leaks are consistent.</p><h2>Where I Draw the Line</h2><p>For simple use cases — a single LLM call with a prompt template — I use the OpenAI SDK directly. LangChain earns its complexity when you need multi-step chains, conditional routing, or agent loops.</p>',
 'published', '2024-10-22 09:00:00'),

('Optimising PHP 8.3 for Production: A Practical Guide',
 'optimising-php-83-production',
 'PHP 8.3 has powerful performance features that most developers are not using. Here is how I configure PHP-FPM, OPcache, and JIT for maximum throughput.',
 '<p>PHP has a performance reputation problem that is completely outdated. A properly tuned PHP 8.3 application can handle thousands of requests per second on modest hardware. Here is my production configuration.</p><h2>OPcache Configuration</h2><p>The most impactful change you can make. Set <code>opcache.memory_consumption=256</code>, <code>opcache.max_accelerated_files=20000</code>, and critically, <code>opcache.validate_timestamps=0</code> in production. This last setting stops PHP from checking file modification times on every request.</p><h2>JIT Compiler</h2><p>Enabled in PHP 8 but often left at default. For CPU-intensive workloads, set <code>opcache.jit=tracing</code> and <code>opcache.jit_buffer_size=128M</code>. For I/O-bound web apps, the gains are modest but measurable.</p>',
 'published', '2024-09-08 09:00:00');

-- ── SEO Meta for key pages ────────────────────────────────────
INSERT INTO `seo_meta` (`page_slug`, `meta_title`, `meta_description`, `og_title`, `og_description`, `robots_directive`) VALUES
('home',
 'Saizul Amin — AI Engineer & Full-Stack Developer',
 'Building intelligent AI systems and scalable web applications. Python, PyTorch, LangChain, PHP 8, React. Available for freelance and consulting.',
 'Saizul Amin — AI Engineer & Full-Stack Developer',
 'Intelligent AI systems. Scalable full-stack applications. 5+ years, 20+ projects delivered.',
 'index, follow'),
('about',
 'About Saizul Amin — AI Engineer from Malaysia',
 'Learn about Saizul Amin: AI engineer, full-stack developer, and builder of production AI systems. Background, skills, values, and what drives the work.',
 'About Saizul Amin',
 'AI engineer and full-stack developer. 5+ years building intelligent systems.',
 'index, follow'),
('ai-projects',
 'AI Projects — Saizul Amin',
 'Production AI systems: RAG pipelines, LLM agents, fine-tuned models, and conversational AI. Real projects with real impact.',
 'AI Projects by Saizul Amin',
 'RAG systems, LLM agents, and production ML deployments.',
 'index, follow'),
('blog',
 'Blog — Saizul Amin',
 'Practical insights on AI engineering, LLM systems, full-stack development, and building production software.',
 'Blog by Saizul Amin',
 'AI engineering, LLM systems, and full-stack development insights.',
 'index, follow')
ON DUPLICATE KEY UPDATE `meta_title` = VALUES(`meta_title`), `meta_description` = VALUES(`meta_description`);
