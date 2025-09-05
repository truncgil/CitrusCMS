# CitrusCMS - İçerik Yönetim Sistemi Solution Design

## 📋 Proje Genel Bakış

**CitrusCMS**, modern web siteleri geliştirmek için tasarlanmış, kullanıcı dostu bir içerik yönetim sistemidir. WordPress benzeri ancak daha basit ve modern teknolojilerle geliştirilmiş, frontend-backend ayrımı yapılmış bir sistemdir.

### 🎯 Temel Hedefler
- Hızlı web sitesi geliştirme
- Kullanıcı dostu yönetim paneli
- Modern teknoloji stack'i
- Docker ile kolay kurulum
- API-first yaklaşım
- SOLID prensiplerine uygun mimari

## 🏗️ Sistem Mimarisi

### Teknoloji Stack'i

#### Backend
- **Framework**: Laravel 11.x
- **Veritabanı**: MySQL 8.0
- **API**: GraphQL (Lighthouse) + REST API
- **Authentication**: Laravel Sanctum
- **File Storage**: Laravel Storage (S3 desteği)
- **Cache**: Redis
- **Queue**: Laravel Queue (Redis backend)

#### Frontend
- **Framework**: React 18 + TypeScript
- **Build Tool**: Vite
- **State Management**: Zustand
- **UI Library**: Ant Design
- **Data Grid**: DevExtreme DataGrid (jQuery CDN üzerinden yüklenmeli, lisans sorunu olmaması için dikkat edilmeli)
- **Rich Text Editor**: Ücretsiz ve gömülebilir resim yükleme destekli bir editör (örn. CKEditor 5 Classic, Tiptap veya Editor.js gibi açık kaynak editörler)
- **Form Management**: React Hook Form + Zod

#### DevOps & Infrastructure
- **Containerization**: Docker + Docker Compose
- **Environment**: Nginx + PHP-FPM
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Monitoring**: Laravel Telescope

## 📁 Proje Yapısı

```
CitrusCMS/
├── backend/                    # Laravel Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   └── Resources/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── DTOs/
│   │   └── Exceptions/
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   ├── config/
│   ├── routes/
│   │   ├── api.php
│   │   └── graphql.php
│   ├── storage/
│   └── tests/
├── frontend/                   # React Frontend
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── hooks/
│   │   ├── services/
│   │   ├── stores/
│   │   ├── types/
│   │   └── utils/
│   ├── public/
│   └── package.json
├── docker/                     # Docker Konfigürasyonları
│   ├── nginx/
│   ├── php/
│   └── mysql/
├── docs/                       # Dokümantasyon
│   ├── api/
│   ├── setup/
│   └── user-guide/
├── docker-compose.yml
├── .env.example
└── README.md
```

## 🗄️ Veritabanı Tasarımı

### Ana Tablolar

#### 1. users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'author') DEFAULT 'author',
    avatar VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 2. pages
```sql
CREATE TABLE pages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NULL,
    excerpt TEXT NULL,
    featured_image VARCHAR(255) NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords TEXT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    author_id BIGINT UNSIGNED,
    parent_id BIGINT UNSIGNED NULL,
    template VARCHAR(100) DEFAULT 'default',
    sort_order INT DEFAULT 0,
    is_homepage BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES pages(id) ON DELETE SET NULL
);
```

#### 3. posts
```sql
CREATE TABLE posts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NULL,
    excerpt TEXT NULL,
    featured_image VARCHAR(255) NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords TEXT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    author_id BIGINT UNSIGNED,
    category_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

#### 4. categories
```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    parent_id BIGINT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

#### 5. media
```sql
CREATE TABLE media (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size BIGINT UNSIGNED NOT NULL,
    path VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255) NULL,
    caption TEXT NULL,
    uploaded_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### 6. settings
```sql
CREATE TABLE settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT NULL,
    type ENUM('string', 'text', 'number', 'boolean', 'json') DEFAULT 'string',
    group VARCHAR(100) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 7. menus
```sql
CREATE TABLE menus (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 8. menu_items
```sql
CREATE TABLE menu_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    menu_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(500) NULL,
    target ENUM('_self', '_blank', '_parent', '_top') DEFAULT '_self',
    parent_id BIGINT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE SET NULL
);
```

## 🔧 SOLID Prensiplerine Uygun Mimari

### 1. Single Responsibility Principle (SRP)
Her sınıfın tek bir sorumluluğu olacak:

```php
// UserService - Sadece kullanıcı işlemleri
class UserService
{
    public function createUser(CreateUserDTO $dto): User
    public function updateUser(int $id, UpdateUserDTO $dto): User
    public function deleteUser(int $id): bool
}

// PageService - Sadece sayfa işlemleri
class PageService
{
    public function createPage(CreatePageDTO $dto): Page
    public function updatePage(int $id, UpdatePageDTO $dto): Page
    public function publishPage(int $id): Page
}
```

### 2. Open/Closed Principle (OCP)
Genişletmeye açık, değiştirmeye kapalı:

```php
interface ContentProcessorInterface
{
    public function process(string $content): string;
}

class MarkdownProcessor implements ContentProcessorInterface
class HtmlProcessor implements ContentProcessorInterface
class WysiwygProcessor implements ContentProcessorInterface
```

### 3. Liskov Substitution Principle (LSP)
Alt sınıflar üst sınıfların yerine geçebilmeli:

```php
abstract class BaseRepository
{
    abstract public function find(int $id): ?Model;
    abstract public function create(array $data): Model;
    abstract public function update(int $id, array $data): Model;
}

class UserRepository extends BaseRepository
class PageRepository extends BaseRepository
```

### 4. Interface Segregation Principle (ISP)
Küçük ve özel arayüzler:

```php
interface ReadableRepositoryInterface
{
    public function find(int $id): ?Model;
    public function all(): Collection;
}

interface WritableRepositoryInterface
{
    public function create(array $data): Model;
    public function update(int $id, array $data): Model;
    public function delete(int $id): bool;
}

interface SearchableRepositoryInterface
{
    public function search(string $query): Collection;
}
```

### 5. Dependency Inversion Principle (DIP)
Bağımlılıklar soyutlamalara dayalı:

```php
class PageController
{
    private PageServiceInterface $pageService;
    private FileServiceInterface $fileService;
    
    public function __construct(
        PageServiceInterface $pageService,
        FileServiceInterface $fileService
    ) {
        $this->pageService = $pageService;
        $this->fileService = $fileService;
    }
}
```

## 🚀 Geliştirme Aşamaları

### Faz 1: Temel Altyapı (1-2 Hafta)
1. **Proje Kurulumu**
   - Laravel projesi oluşturma
   - React + Vite kurulumu
   - Docker konfigürasyonu
   - Veritabanı migration'ları

2. **Temel Modeller**
   - User modeli
   - Page modeli
   - Post modeli
   - Category modeli

3. **Authentication Sistemi**
   - Laravel Sanctum kurulumu
   - Login/Register API'leri
   - JWT token yönetimi

### Faz 2: Backend API Geliştirme (2-3 Hafta)
1. **CRUD API'leri**
   - User CRUD
   - Page CRUD
   - Post CRUD
   - Category CRUD

2. **File Management**
   - Dosya yükleme sistemi
   - Media library API
   - Image optimization

3. **Content Management**
   - Rich text editor entegrasyonu
   - Content validation
   - SEO meta fields

### Faz 3: Frontend Geliştirme (3-4 Hafta)
1. **Authentication UI**
   - Login sayfası
   - Register sayfası
   - Password reset

2. **Dashboard**
   - Ana dashboard
   - Sidebar navigation
   - Breadcrumb navigation

3. **Content Management UI**
   - Page editor
   - Post editor
   - Media library
   - Category management

### Faz 4: Gelişmiş Özellikler (2-3 Hafta)
1. **Menu Management**
   - Menu builder
   - Drag & drop interface

2. **Settings Management**
   - Site ayarları
   - Theme ayarları
   - SEO ayarları

3. **User Management**
   - Role-based permissions
   - User profiles
   - Activity logs

### Faz 5: Testing & Optimization (1-2 Hafta)
1. **Testing**
   - Unit tests
   - Integration tests
   - E2E tests

2. **Performance Optimization**
   - Caching
   - Database optimization
   - Frontend optimization

3. **Documentation**
   - API documentation
   - User guide
   - Developer guide

## 📊 AG Grid Entegrasyonu

### Data Grid Konfigürasyonu
```typescript
// pages/DataGrid.tsx
import { AgGridReact } from 'ag-grid-react';
import { ColDef, GridOptions } from 'ag-grid-community';

const columnDefs: ColDef[] = [
    { field: 'id', headerName: 'ID', width: 80 },
    { field: 'title', headerName: 'Başlık', editable: true },
    { field: 'status', headerName: 'Durum', cellRenderer: 'statusRenderer' },
    { field: 'author', headerName: 'Yazar' },
    { field: 'created_at', headerName: 'Oluşturulma Tarihi' },
    { 
        headerName: 'İşlemler',
        cellRenderer: 'actionRenderer',
        width: 150
    }
];

const gridOptions: GridOptions = {
    columnDefs,
    rowData: [],
    pagination: true,
    paginationPageSize: 20,
    enableSorting: true,
    enableFilter: true,
    enableColResize: true,
    enableRangeSelection: true,
    rowSelection: 'multiple'
};
```

## 🔐 API Güvenliği

### Authentication & Authorization
```php
// Middleware/CheckPermission.php
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!auth()->user()->hasPermission($permission)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return $next($request);
    }
}

// Routes/api.php
Route::middleware(['auth:sanctum', 'permission:manage_pages'])->group(function () {
    Route::apiResource('pages', PageController::class);
});
```

## 🐳 Docker Konfigürasyonu

### docker-compose.yml
```yaml
version: '3.8'

services:
  app:
    build:
      context: ./backend
      dockerfile: ../docker/php/Dockerfile
    container_name: citruscms_app
    restart: unless-stopped
    working_dir: /var/www/
    volumes:
      - ./backend:/var/www
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - citruscms

  webserver:
    image: nginx:alpine
    container_name: citruscms_nginx
    restart: unless-stopped
    ports:
      - "8000:80"
    volumes:
      - ./backend:/var/www
      - ./docker/nginx/conf.d/:/etc/nginx/conf.d/
    networks:
      - citruscms

  db:
    image: mysql:8.0
    container_name: citruscms_db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: citruscms
      MYSQL_ROOT_PASSWORD: root
      MYSQL_PASSWORD: citruscms
      MYSQL_USER: citruscms
    volumes:
      - dbdata:/var/lib/mysql/
      - ./docker/mysql/my.cnf:/etc/mysql/my.cnf
    ports:
      - "3306:3306"
    networks:
      - citruscms

  redis:
    image: redis:alpine
    container_name: citruscms_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - citruscms

  frontend:
    build:
      context: ./frontend
      dockerfile: ../docker/node/Dockerfile
    container_name: citruscms_frontend
    restart: unless-stopped
    ports:
      - "3000:3000"
    volumes:
      - ./frontend:/app
      - /app/node_modules
    networks:
      - citruscms

networks:
  citruscms:
    driver: bridge

volumes:
  dbdata:
    driver: local
```

## 📝 API Dokümantasyonu

### GraphQL Schema
```graphql
type User {
  id: ID!
  name: String!
  email: String!
  role: UserRole!
  avatar: String
  isActive: Boolean!
  createdAt: DateTime!
  updatedAt: DateTime!
}

type Page {
  id: ID!
  title: String!
  slug: String!
  content: String
  excerpt: String
  featuredImage: String
  status: PageStatus!
  author: User!
  parent: Page
  template: String!
  isHomepage: Boolean!
  createdAt: DateTime!
  updatedAt: DateTime!
}

type Query {
  users: [User!]!
  user(id: ID!): User
  pages: [Page!]!
  page(slug: String!): Page
  posts: [Post!]!
  categories: [Category!]!
}

type Mutation {
  createUser(input: CreateUserInput!): User!
  updateUser(id: ID!, input: UpdateUserInput!): User!
  deleteUser(id: ID!): Boolean!
  
  createPage(input: CreatePageInput!): Page!
  updatePage(id: ID!, input: UpdatePageInput!): Page!
  deletePage(id: ID!): Boolean!
  publishPage(id: ID!): Page!
}
```

## 🎨 Frontend Tasarım Sistemi

### Component Library
```typescript
// components/ui/Button.tsx
interface ButtonProps {
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  loading?: boolean;
  disabled?: boolean;
  children: React.ReactNode;
  onClick?: () => void;
}

// components/ui/Modal.tsx
interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  title: string;
  children: React.ReactNode;
  size?: 'sm' | 'md' | 'lg' | 'xl';
}

// components/ui/DataTable.tsx
interface DataTableProps {
  data: any[];
  columns: ColumnDef[];
  loading?: boolean;
  onRowClick?: (row: any) => void;
  onSelectionChange?: (selectedRows: any[]) => void;
}
```

## 🔄 State Management

### Zustand Store
```typescript
// stores/authStore.ts
interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  login: (credentials: LoginCredentials) => Promise<void>;
  logout: () => void;
  updateUser: (user: User) => void;
}

// stores/pageStore.ts
interface PageState {
  pages: Page[];
  currentPage: Page | null;
  loading: boolean;
  fetchPages: () => Promise<void>;
  createPage: (page: CreatePageDTO) => Promise<void>;
  updatePage: (id: number, page: UpdatePageDTO) => Promise<void>;
  deletePage: (id: number) => Promise<void>;
}
```

## 🧪 Testing Stratejisi

### Backend Testing
```php
// tests/Feature/PageTest.php
class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_page()
    {
        $user = User::factory()->create();
        $pageData = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Test content'
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/pages', $pageData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pages', $pageData);
    }
}
```

### Frontend Testing
```typescript
// tests/components/PageEditor.test.tsx
import { render, screen, fireEvent } from '@testing-library/react';
import PageEditor from '../components/PageEditor';

describe('PageEditor', () => {
  it('should save page when form is submitted', async () => {
    const mockSave = jest.fn();
    render(<PageEditor onSave={mockSave} />);
    
    fireEvent.change(screen.getByLabelText('Başlık'), {
      target: { value: 'Test Page' }
    });
    
    fireEvent.click(screen.getByText('Kaydet'));
    
    expect(mockSave).toHaveBeenCalledWith({
      title: 'Test Page'
    });
  });
});
```

## 📈 Performance Optimizasyonu

### Backend Optimizasyonu
```php
// Caching Strategy
class PageService
{
    public function getPage(string $slug): Page
    {
        return Cache::remember("page:{$slug}", 3600, function () use ($slug) {
            return Page::where('slug', $slug)
                ->with(['author', 'parent'])
                ->firstOrFail();
        });
    }
}

// Database Optimization
class Page extends Model
{
    protected $casts = [
        'published_at' => 'datetime',
        'is_homepage' => 'boolean',
    ];
    
    protected $with = ['author'];
    
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at');
    }
}
```

### Frontend Optimizasyonu
```typescript
// React.memo for performance
const PageList = React.memo(({ pages, onPageClick }: PageListProps) => {
  return (
    <div className="page-list">
      {pages.map(page => (
        <PageCard key={page.id} page={page} onClick={onPageClick} />
      ))}
    </div>
  );
});

// Lazy loading
const PageEditor = React.lazy(() => import('./PageEditor'));
const MediaLibrary = React.lazy(() => import('./MediaLibrary'));
```

## 🚀 Deployment Stratejisi

### Production Environment
```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  app:
    build:
      context: ./backend
      dockerfile: ../docker/php/Dockerfile.prod
    environment:
      APP_ENV: production
      APP_DEBUG: false
      CACHE_DRIVER: redis
      SESSION_DRIVER: redis
      QUEUE_CONNECTION: redis

  webserver:
    image: nginx:alpine
    volumes:
      - ./backend:/var/www
      - ./docker/nginx/prod.conf:/etc/nginx/conf.d/default.conf
    ports:
      - "80:80"
      - "443:443"

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: citruscms_prod
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_USER: ${DB_USER}
    volumes:
      - mysql_data:/var/lib/mysql
```

## 📚 Dokümantasyon Yapısı

```
docs/
├── api/
│   ├── authentication.md
│   ├── pages.md
│   ├── posts.md
│   ├── media.md
│   └── graphql.md
├── setup/
│   ├── installation.md
│   ├── docker-setup.md
│   ├── development.md
│   └── production.md
├── user-guide/
│   ├── dashboard.md
│   ├── content-management.md
│   ├── media-library.md
│   └── settings.md
└── developer/
    ├── architecture.md
    ├── contributing.md
    ├── testing.md
    └── deployment.md
```

## 🎯 Sonraki Adımlar

1. **Proje Kurulumu**
   - Repository oluşturma
   - Docker environment hazırlama
   - Development team onboarding

2. **Sprint Planlama**
   - 2 haftalık sprint'ler
   - Her sprint'te demo
   - Retrospective toplantıları

3. **Quality Assurance**
   - Code review süreci
   - Automated testing
   - Performance monitoring

4. **Documentation**
   - API documentation
   - User manual
   - Developer guide

Bu solution design, CitrusCMS'in başarılı bir şekilde geliştirilmesi için gerekli tüm teknik detayları ve iş akışını içermektedir. SOLID prensiplerine uygun, modern teknolojilerle geliştirilmiş, ölçeklenebilir bir sistem oluşturulacaktır. 