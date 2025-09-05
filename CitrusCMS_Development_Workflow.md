# CitrusCMS - Geliştirme İş Akışı ve Aşamalar

## 🎯 Proje Başlangıç Süreci

### 1. Proje Kurulumu (Hafta 1)

#### 1.1 Repository ve Proje Yapısı
```bash
# Ana proje dizini oluşturma
mkdir CitrusCMS
cd CitrusCMS

# Backend (Laravel) kurulumu
composer create-project laravel/laravel backend
cd backend
composer require laravel/sanctum
composer require nuwave/lighthouse
composer require spatie/laravel-permission

# Frontend (React + Vite) kurulumu
cd ..
npm create vite@latest frontend -- --template react-ts
cd frontend
npm install
npm install @ag-grid-community/react @ag-grid-community/core
npm install antd @ant-design/icons
npm install zustand
npm install react-hook-form @hookform/resolvers zod
npm install react-router-dom
npm install axios
```

#### 1.2 Docker Konfigürasyonu
```bash
# Docker dosyaları oluşturma
mkdir docker
mkdir docker/nginx
mkdir docker/php
mkdir docker/mysql
mkdir docker/node

# Docker Compose dosyası
touch docker-compose.yml
touch docker-compose.dev.yml
touch docker-compose.prod.yml
```

#### 1.3 Veritabanı Migration'ları
```bash
cd backend
php artisan make:migration create_users_table
php artisan make:migration create_pages_table
php artisan make:migration create_posts_table
php artisan make:migration create_categories_table
php artisan make:migration create_media_table
php artisan make:migration create_settings_table
php artisan make:migration create_menus_table
php artisan make:migration create_menu_items_table
```

### 2. Temel Modeller ve İlişkiler (Hafta 1-2)

#### 2.1 Model Oluşturma
```bash
# Model'ler oluşturma
php artisan make:model User
php artisan make:model Page
php artisan make:model Post
php artisan make:model Category
php artisan make:model Media
php artisan make:model Setting
php artisan make:model Menu
php artisan make:model MenuItem
```

#### 2.2 Service Layer Oluşturma
```bash
# Service'ler oluşturma
mkdir app/Services
touch app/Services/UserService.php
touch app/Services/PageService.php
touch app/Services/PostService.php
touch app/Services/MediaService.php
touch app/Services/SettingService.php
```

#### 2.3 Repository Pattern
```bash
# Repository'ler oluşturma
mkdir app/Repositories
mkdir app/Repositories/Interfaces
touch app/Repositories/Interfaces/UserRepositoryInterface.php
touch app/Repositories/Interfaces/PageRepositoryInterface.php
touch app/Repositories/UserRepository.php
touch app/Repositories/PageRepository.php
```

### 3. Authentication Sistemi (Hafta 2)

#### 3.1 Laravel Sanctum Kurulumu
```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

#### 3.2 Authentication Controller
```bash
php artisan make:controller Auth/AuthController
```

#### 3.3 JWT Token Yönetimi
```php
// app/Http/Controllers/Auth/AuthController.php
class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;
            
            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        }
        
        return response()->json(['error' => 'Invalid credentials'], 401);
    }
}
```

## 🚀 Backend API Geliştirme (Hafta 3-5)

### 1. CRUD API'leri Geliştirme

#### 1.1 Controller'lar Oluşturma
```bash
# API Controller'ları
php artisan make:controller Api/UserController --api
php artisan make:controller Api/PageController --api
php artisan make:controller Api/PostController --api
php artisan make:controller Api/CategoryController --api
php artisan make:controller Api/MediaController --api
php artisan make:controller Api/SettingController --api
```

#### 1.2 Resource'lar Oluşturma
```bash
# API Resource'ları
php artisan make:resource UserResource
php artisan make:resource PageResource
php artisan make:resource PostResource
php artisan make:resource CategoryResource
php artisan make:resource MediaResource
```

#### 1.3 Request Validation
```bash
# Request sınıfları
php artisan make:request CreateUserRequest
php artisan make:request UpdateUserRequest
php artisan make:request CreatePageRequest
php artisan make:request UpdatePageRequest
```

### 2. File Management Sistemi

#### 2.1 Media Upload Service
```php
// app/Services/MediaService.php
class MediaService
{
    public function uploadFile(UploadedFile $file, string $directory = 'media'): Media
    {
        $filename = $this->generateUniqueFilename($file);
        $path = $file->storeAs($directory, $filename, 'public');
        
        return Media::create([
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'uploaded_by' => auth()->id()
        ]);
    }
    
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return uniqid() . '_' . time() . '.' . $extension;
    }
}
```

#### 2.2 Image Optimization
```php
// app/Services/ImageService.php
class ImageService
{
    public function optimizeImage(string $path, array $sizes = []): array
    {
        $image = Image::make(storage_path('app/public/' . $path));
        $optimizedPaths = [];
        
        foreach ($sizes as $size => $dimensions) {
            $resized = $image->resize($dimensions['width'], $dimensions['height']);
            $newPath = $this->generateResizedPath($path, $size);
            $resized->save(storage_path('app/public/' . $newPath));
            $optimizedPaths[$size] = $newPath;
        }
        
        return $optimizedPaths;
    }
}
```

### 3. Content Management API

#### 3.1 Rich Text Editor Entegrasyonu
```php
// app/Services/ContentService.php
class ContentService
{
    public function processContent(string $content): string
    {
        // HTML sanitization
        $content = $this->sanitizeHtml($content);
        
        // Image optimization
        $content = $this->optimizeImages($content);
        
        // Link processing
        $content = $this->processLinks($content);
        
        return $content;
    }
    
    private function sanitizeHtml(string $html): string
    {
        return clean($html, [
            'HTML.Allowed' => 'p,br,strong,em,u,h1,h2,h3,h4,h5,h6,ul,ol,li,a[href],img[src|alt],blockquote,pre,code'
        ]);
    }
}
```

## 🎨 Frontend Geliştirme (Hafta 6-9)

### 1. Authentication UI

#### 1.1 Login Sayfası
```typescript
// src/pages/Login.tsx
import React from 'react';
import { Form, Input, Button, Card, message } from 'antd';
import { useAuthStore } from '../stores/authStore';

const Login: React.FC = () => {
  const { login, loading } = useAuthStore();
  
  const onFinish = async (values: any) => {
    try {
      await login(values);
      message.success('Başarıyla giriş yapıldı');
    } catch (error) {
      message.error('Giriş başarısız');
    }
  };
  
  return (
    <div className="login-container">
      <Card title="CitrusCMS Giriş" className="login-card">
        <Form onFinish={onFinish} layout="vertical">
          <Form.Item
            name="email"
            label="E-posta"
            rules={[{ required: true, type: 'email' }]}
          >
            <Input />
          </Form.Item>
          
          <Form.Item
            name="password"
            label="Şifre"
            rules={[{ required: true }]}
          >
            <Input.Password />
          </Form.Item>
          
          <Form.Item>
            <Button type="primary" htmlType="submit" loading={loading} block>
              Giriş Yap
            </Button>
          </Form.Item>
        </Form>
      </Card>
    </div>
  );
};
```

#### 1.2 Auth Store (Zustand)
```typescript
// src/stores/authStore.ts
import { create } from 'zustand';
import { api } from '../services/api';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
}

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  loading: boolean;
  login: (credentials: { email: string; password: string }) => Promise<void>;
  logout: () => void;
  checkAuth: () => Promise<void>;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  token: localStorage.getItem('token'),
  isAuthenticated: false,
  loading: false,
  
  login: async (credentials) => {
    set({ loading: true });
    try {
      const response = await api.post('/auth/login', credentials);
      const { user, token } = response.data;
      
      localStorage.setItem('token', token);
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      
      set({ user, token, isAuthenticated: true, loading: false });
    } catch (error) {
      set({ loading: false });
      throw error;
    }
  },
  
  logout: () => {
    localStorage.removeItem('token');
    delete api.defaults.headers.common['Authorization'];
    set({ user: null, token: null, isAuthenticated: false });
  },
  
  checkAuth: async () => {
    const token = get().token;
    if (!token) return;
    
    try {
      const response = await api.get('/auth/user');
      set({ user: response.data, isAuthenticated: true });
    } catch (error) {
      get().logout();
    }
  }
}));
```

### 2. Dashboard Layout

#### 2.1 Ana Layout Bileşeni
```typescript
// src/components/Layout/MainLayout.tsx
import React from 'react';
import { Layout, Menu, Avatar, Dropdown } from 'antd';
import { useAuthStore } from '../../stores/authStore';
import { Sidebar } from './Sidebar';
import { Header } from './Header';

const { Content } = Layout;

interface MainLayoutProps {
  children: React.ReactNode;
}

export const MainLayout: React.FC<MainLayoutProps> = ({ children }) => {
  const { user, logout } = useAuthStore();
  
  return (
    <Layout style={{ minHeight: '100vh' }}>
      <Sidebar />
      <Layout>
        <Header user={user} onLogout={logout} />
        <Content style={{ margin: '24px 16px', padding: 24, background: '#fff' }}>
          {children}
        </Content>
      </Layout>
    </Layout>
  );
};
```

#### 2.2 Sidebar Navigation
```typescript
// src/components/Layout/Sidebar.tsx
import React from 'react';
import { Layout, Menu } from 'antd';
import { useNavigate, useLocation } from 'react-router-dom';
import {
  DashboardOutlined,
  FileTextOutlined,
  PictureOutlined,
  SettingOutlined,
  UserOutlined
} from '@ant-design/icons';

const { Sider } = Layout;

export const Sidebar: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  
  const menuItems = [
    {
      key: '/dashboard',
      icon: <DashboardOutlined />,
      label: 'Dashboard'
    },
    {
      key: '/pages',
      icon: <FileTextOutlined />,
      label: 'Sayfalar'
    },
    {
      key: '/posts',
      icon: <FileTextOutlined />,
      label: 'Yazılar'
    },
    {
      key: '/media',
      icon: <PictureOutlined />,
      label: 'Medya'
    },
    {
      key: '/users',
      icon: <UserOutlined />,
      label: 'Kullanıcılar'
    },
    {
      key: '/settings',
      icon: <SettingOutlined />,
      label: 'Ayarlar'
    }
  ];
  
  return (
    <Sider width={250} theme="dark">
      <div className="logo" style={{ height: 64, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        <h2 style={{ color: 'white', margin: 0 }}>CitrusCMS</h2>
      </div>
      <Menu
        theme="dark"
        mode="inline"
        selectedKeys={[location.pathname]}
        items={menuItems}
        onClick={({ key }) => navigate(key)}
      />
    </Sider>
  );
};
```

### 3. Content Management UI

#### 3.1 Page Editor
```typescript
// src/pages/PageEditor.tsx
import React, { useState, useEffect } from 'react';
import { Form, Input, Button, Card, message, Select } from 'antd';
import { Editor } from '@tinymce/tinymce-react';
import { useParams, useNavigate } from 'react-router-dom';
import { api } from '../services/api';

const { TextArea } = Input;
const { Option } = Select;

interface PageFormData {
  title: string;
  slug: string;
  content: string;
  excerpt: string;
  status: 'draft' | 'published' | 'archived';
  template: string;
}

export const PageEditor: React.FC = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [form] = Form.useForm();
  const [loading, setLoading] = useState(false);
  const [initialData, setInitialData] = useState<PageFormData | null>(null);
  
  useEffect(() => {
    if (id) {
      loadPage();
    }
  }, [id]);
  
  const loadPage = async () => {
    try {
      const response = await api.get(`/pages/${id}`);
      setInitialData(response.data);
      form.setFieldsValue(response.data);
    } catch (error) {
      message.error('Sayfa yüklenemedi');
    }
  };
  
  const onFinish = async (values: PageFormData) => {
    setLoading(true);
    try {
      if (id) {
        await api.put(`/pages/${id}`, values);
        message.success('Sayfa güncellendi');
      } else {
        await api.post('/pages', values);
        message.success('Sayfa oluşturuldu');
      }
      navigate('/pages');
    } catch (error) {
      message.error('Bir hata oluştu');
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <Card title={id ? 'Sayfa Düzenle' : 'Yeni Sayfa'}>
      <Form
        form={form}
        layout="vertical"
        onFinish={onFinish}
        initialValues={initialData}
      >
        <Form.Item
          name="title"
          label="Başlık"
          rules={[{ required: true, message: 'Başlık gerekli' }]}
        >
          <Input />
        </Form.Item>
        
        <Form.Item
          name="slug"
          label="URL"
          rules={[{ required: true, message: 'URL gerekli' }]}
        >
          <Input />
        </Form.Item>
        
        <Form.Item
          name="excerpt"
          label="Özet"
        >
          <TextArea rows={3} />
        </Form.Item>
        
        <Form.Item
          name="content"
          label="İçerik"
        >
          <Editor
            apiKey="your-tinymce-api-key"
            init={{
              height: 500,
              menubar: false,
              plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
              ],
              toolbar: 'undo redo | blocks | ' +
                'bold italic forecolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help'
            }}
          />
        </Form.Item>
        
        <Form.Item
          name="status"
          label="Durum"
          rules={[{ required: true }]}
        >
          <Select>
            <Option value="draft">Taslak</Option>
            <Option value="published">Yayınlandı</Option>
            <Option value="archived">Arşivlendi</Option>
          </Select>
        </Form.Item>
        
        <Form.Item
          name="template"
          label="Şablon"
          rules={[{ required: true }]}
        >
          <Select>
            <Option value="default">Varsayılan</Option>
            <Option value="full-width">Tam Genişlik</Option>
            <Option value="sidebar">Kenar Çubuklu</Option>
          </Select>
        </Form.Item>
        
        <Form.Item>
          <Button type="primary" htmlType="submit" loading={loading}>
            {id ? 'Güncelle' : 'Oluştur'}
          </Button>
          <Button style={{ marginLeft: 8 }} onClick={() => navigate('/pages')}>
            İptal
          </Button>
        </Form.Item>
      </Form>
    </Card>
  );
};
```

#### 3.2 Data Grid (AG Grid)
```typescript
// src/components/DataGrid/PagesGrid.tsx
import React, { useState, useEffect } from 'react';
import { AgGridReact } from 'ag-grid-react';
import { ColDef, GridOptions } from 'ag-grid-community';
import { Button, Space, Tag } from 'antd';
import { EditOutlined, DeleteOutlined, EyeOutlined } from '@ant-design/icons';
import { api } from '../../services/api';

interface Page {
  id: number;
  title: string;
  slug: string;
  status: string;
  author: string;
  created_at: string;
}

export const PagesGrid: React.FC = () => {
  const [rowData, setRowData] = useState<Page[]>([]);
  const [loading, setLoading] = useState(false);
  
  const columnDefs: ColDef[] = [
    { 
      field: 'id', 
      headerName: 'ID', 
      width: 80,
      sortable: true,
      filter: true
    },
    { 
      field: 'title', 
      headerName: 'Başlık', 
      editable: true,
      sortable: true,
      filter: true
    },
    { 
      field: 'slug', 
      headerName: 'URL', 
      sortable: true,
      filter: true
    },
    { 
      field: 'status', 
      headerName: 'Durum',
      cellRenderer: (params: any) => {
        const status = params.value;
        const color = status === 'published' ? 'green' : 
                     status === 'draft' ? 'orange' : 'red';
        return <Tag color={color}>{status}</Tag>;
      },
      sortable: true,
      filter: true
    },
    { 
      field: 'author', 
      headerName: 'Yazar',
      sortable: true,
      filter: true
    },
    { 
      field: 'created_at', 
      headerName: 'Oluşturulma Tarihi',
      valueFormatter: (params: any) => {
        return new Date(params.value).toLocaleDateString('tr-TR');
      },
      sortable: true,
      filter: true
    },
    { 
      headerName: 'İşlemler',
      cellRenderer: (params: any) => {
        return (
          <Space>
            <Button 
              type="link" 
              icon={<EyeOutlined />}
              onClick={() => handleView(params.data)}
            />
            <Button 
              type="link" 
              icon={<EditOutlined />}
              onClick={() => handleEdit(params.data)}
            />
            <Button 
              type="link" 
              danger 
              icon={<DeleteOutlined />}
              onClick={() => handleDelete(params.data)}
            />
          </Space>
        );
      },
      width: 150,
      sortable: false,
      filter: false
    }
  ];
  
  const gridOptions: GridOptions = {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    enableSorting: true,
    enableFilter: true,
    enableColResize: true,
    enableRangeSelection: true,
    rowSelection: 'multiple',
    animateRows: true,
    defaultColDef: {
      resizable: true,
      sortable: true,
      filter: true
    }
  };
  
  useEffect(() => {
    loadPages();
  }, []);
  
  const loadPages = async () => {
    setLoading(true);
    try {
      const response = await api.get('/pages');
      setRowData(response.data);
    } catch (error) {
      console.error('Sayfalar yüklenemedi:', error);
    } finally {
      setLoading(false);
    }
  };
  
  const handleView = (page: Page) => {
    // Sayfa önizleme modal'ı aç
  };
  
  const handleEdit = (page: Page) => {
    // Düzenleme sayfasına yönlendir
  };
  
  const handleDelete = async (page: Page) => {
    // Silme işlemi
  };
  
  return (
    <div className="ag-theme-alpine" style={{ height: 600, width: '100%' }}>
      <AgGridReact
        {...gridOptions}
        loading={loading}
      />
    </div>
  );
};
```

## 🔧 Gelişmiş Özellikler (Hafta 10-12)

### 1. Menu Management

#### 1.1 Menu Builder Component
```typescript
// src/components/MenuBuilder/MenuBuilder.tsx
import React, { useState } from 'react';
import { Card, Button, Tree, Input, Modal, Form } from 'antd';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

interface MenuItem {
  id: string;
  title: string;
  url: string;
  children?: MenuItem[];
}

export const MenuBuilder: React.FC = () => {
  const [menuItems, setMenuItems] = useState<MenuItem[]>([]);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [form] = Form.useForm();
  
  const onDragEnd = (result: any) => {
    if (!result.destination) return;
    
    const items = Array.from(menuItems);
    const [reorderedItem] = items.splice(result.source.index, 1);
    items.splice(result.destination.index, 0, reorderedItem);
    
    setMenuItems(items);
  };
  
  const addMenuItem = (values: any) => {
    const newItem: MenuItem = {
      id: Date.now().toString(),
      title: values.title,
      url: values.url
    };
    
    setMenuItems([...menuItems, newItem]);
    setIsModalVisible(false);
    form.resetFields();
  };
  
  return (
    <Card title="Menü Oluşturucu">
      <Button 
        type="primary" 
        onClick={() => setIsModalVisible(true)}
        style={{ marginBottom: 16 }}
      >
        Menü Öğesi Ekle
      </Button>
      
      <DragDropContext onDragEnd={onDragEnd}>
        <Droppable droppableId="menu-items">
          {(provided) => (
            <div {...provided.droppableProps} ref={provided.innerRef}>
              {menuItems.map((item, index) => (
                <Draggable key={item.id} draggableId={item.id} index={index}>
                  {(provided) => (
                    <div
                      ref={provided.innerRef}
                      {...provided.draggableProps}
                      {...provided.dragHandleProps}
                      style={{
                        padding: 8,
                        margin: '0 0 8px 0',
                        backgroundColor: '#fafafa',
                        border: '1px solid #d9d9d9',
                        borderRadius: 6
                      }}
                    >
                      {item.title} - {item.url}
                    </div>
                  )}
                </Draggable>
              ))}
              {provided.placeholder}
            </div>
          )}
        </Droppable>
      </DragDropContext>
      
      <Modal
        title="Menü Öğesi Ekle"
        open={isModalVisible}
        onCancel={() => setIsModalVisible(false)}
        footer={null}
      >
        <Form form={form} onFinish={addMenuItem} layout="vertical">
          <Form.Item
            name="title"
            label="Başlık"
            rules={[{ required: true }]}
          >
            <Input />
          </Form.Item>
          
          <Form.Item
            name="url"
            label="URL"
            rules={[{ required: true }]}
          >
            <Input />
          </Form.Item>
          
          <Form.Item>
            <Button type="primary" htmlType="submit">
              Ekle
            </Button>
          </Form.Item>
        </Form>
      </Modal>
    </Card>
  );
};
```

### 2. Settings Management

#### 2.1 Settings Form
```typescript
// src/pages/Settings.tsx
import React, { useState, useEffect } from 'react';
import { Form, Input, Button, Card, Tabs, Switch, message } from 'antd';
import { api } from '../services/api';

const { TabPane } = Tabs;
const { TextArea } = Input;

interface Settings {
  site_title: string;
  site_description: string;
  site_keywords: string;
  contact_email: string;
  contact_phone: string;
  social_facebook: string;
  social_twitter: string;
  social_instagram: string;
  analytics_google: string;
  seo_robots: string;
  maintenance_mode: boolean;
}

export const Settings: React.FC = () => {
  const [form] = Form.useForm();
  const [loading, setLoading] = useState(false);
  const [settings, setSettings] = useState<Settings | null>(null);
  
  useEffect(() => {
    loadSettings();
  }, []);
  
  const loadSettings = async () => {
    try {
      const response = await api.get('/settings');
      setSettings(response.data);
      form.setFieldsValue(response.data);
    } catch (error) {
      message.error('Ayarlar yüklenemedi');
    }
  };
  
  const onFinish = async (values: Settings) => {
    setLoading(true);
    try {
      await api.put('/settings', values);
      message.success('Ayarlar güncellendi');
    } catch (error) {
      message.error('Ayarlar güncellenemedi');
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <Card title="Site Ayarları">
      <Form
        form={form}
        layout="vertical"
        onFinish={onFinish}
        initialValues={settings}
      >
        <Tabs defaultActiveKey="general">
          <TabPane tab="Genel" key="general">
            <Form.Item
              name="site_title"
              label="Site Başlığı"
              rules={[{ required: true }]}
            >
              <Input />
            </Form.Item>
            
            <Form.Item
              name="site_description"
              label="Site Açıklaması"
            >
              <TextArea rows={3} />
            </Form.Item>
            
            <Form.Item
              name="site_keywords"
              label="Anahtar Kelimeler"
            >
              <Input />
            </Form.Item>
          </TabPane>
          
          <TabPane tab="İletişim" key="contact">
            <Form.Item
              name="contact_email"
              label="E-posta"
              rules={[{ type: 'email' }]}
            >
              <Input />
            </Form.Item>
            
            <Form.Item
              name="contact_phone"
              label="Telefon"
            >
              <Input />
            </Form.Item>
          </TabPane>
          
          <TabPane tab="Sosyal Medya" key="social">
            <Form.Item
              name="social_facebook"
              label="Facebook"
            >
              <Input />
            </Form.Item>
            
            <Form.Item
              name="social_twitter"
              label="Twitter"
            >
              <Input />
            </Form.Item>
            
            <Form.Item
              name="social_instagram"
              label="Instagram"
            >
              <Input />
            </Form.Item>
          </TabPane>
          
          <TabPane tab="SEO" key="seo">
            <Form.Item
              name="analytics_google"
              label="Google Analytics Kodu"
            >
              <TextArea rows={4} />
            </Form.Item>
            
            <Form.Item
              name="seo_robots"
              label="Robots.txt İçeriği"
            >
              <TextArea rows={6} />
            </Form.Item>
          </TabPane>
          
          <TabPane tab="Sistem" key="system">
            <Form.Item
              name="maintenance_mode"
              label="Bakım Modu"
              valuePropName="checked"
            >
              <Switch />
            </Form.Item>
          </TabPane>
        </Tabs>
        
        <Form.Item>
          <Button type="primary" htmlType="submit" loading={loading}>
            Ayarları Kaydet
          </Button>
        </Form.Item>
      </Form>
    </Card>
  );
};
```

## 🧪 Testing Stratejisi (Hafta 13-14)

### 1. Backend Testing

#### 1.1 Unit Tests
```php
// tests/Unit/Services/PageServiceTest.php
class PageServiceTest extends TestCase
{
    use RefreshDatabase;
    
    private PageService $pageService;
    private User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->pageService = app(PageService::class);
        $this->user = User::factory()->create();
    }
    
    public function test_can_create_page()
    {
        $pageData = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Test content',
            'author_id' => $this->user->id
        ];
        
        $page = $this->pageService->createPage($pageData);
        
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('Test Page', $page->title);
        $this->assertEquals('test-page', $page->slug);
    }
    
    public function test_can_update_page()
    {
        $page = Page::factory()->create(['author_id' => $this->user->id]);
        
        $updateData = [
            'title' => 'Updated Page',
            'content' => 'Updated content'
        ];
        
        $updatedPage = $this->pageService->updatePage($page->id, $updateData);
        
        $this->assertEquals('Updated Page', $updatedPage->title);
        $this->assertEquals('Updated content', $updatedPage->content);
    }
}
```

#### 1.2 Feature Tests
```php
// tests/Feature/Api/PageControllerTest.php
class PageControllerTest extends TestCase
{
    use RefreshDatabase;
    
    private User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
    
    public function test_can_list_pages()
    {
        Page::factory()->count(3)->create(['author_id' => $this->user->id]);
        
        $response = $this->actingAs($this->user)
            ->getJson('/api/pages');
        
        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }
    
    public function test_can_create_page()
    {
        $pageData = [
            'title' => 'New Page',
            'slug' => 'new-page',
            'content' => 'Page content'
        ];
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/pages', $pageData);
        
        $response->assertStatus(201)
                ->assertJsonFragment(['title' => 'New Page']);
    }
}
```

### 2. Frontend Testing

#### 2.1 Component Tests
```typescript
// src/__tests__/components/PageEditor.test.tsx
import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { PageEditor } from '../../pages/PageEditor';
import { api } from '../../services/api';

// Mock API
jest.mock('../../services/api');

describe('PageEditor', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });
  
  it('should render form fields', () => {
    render(
      <BrowserRouter>
        <PageEditor />
      </BrowserRouter>
    );
    
    expect(screen.getByLabelText('Başlık')).toBeInTheDocument();
    expect(screen.getByLabelText('URL')).toBeInTheDocument();
    expect(screen.getByLabelText('İçerik')).toBeInTheDocument();
  });
  
  it('should submit form with correct data', async () => {
    const mockPost = api.post as jest.MockedFunction<typeof api.post>;
    mockPost.mockResolvedValueOnce({ data: { id: 1 } });
    
    render(
      <BrowserRouter>
        <PageEditor />
      </BrowserRouter>
    );
    
    fireEvent.change(screen.getByLabelText('Başlık'), {
      target: { value: 'Test Page' }
    });
    
    fireEvent.change(screen.getByLabelText('URL'), {
      target: { value: 'test-page' }
    });
    
    fireEvent.click(screen.getByText('Oluştur'));
    
    await waitFor(() => {
      expect(mockPost).toHaveBeenCalledWith('/pages', {
        title: 'Test Page',
        slug: 'test-page'
      });
    });
  });
});
```

## 🚀 Deployment ve Production (Hafta 15)

### 1. Docker Production Build
```dockerfile
# docker/php/Dockerfile.prod
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY ./backend /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data ./backend /var/www

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Change current user to www
USER www-data

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
```

### 2. Production Environment Variables
```bash
# .env.production
APP_NAME=CitrusCMS
APP_ENV=production
APP_KEY=base64:your-production-key
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=citruscms_prod
DB_USERNAME=citruscms
DB_PASSWORD=your-secure-password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. CI/CD Pipeline
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        
    - name: Install dependencies
      run: |
        cd backend
        composer install
        
    - name: Run tests
      run: |
        cd backend
        php artisan test
        
  deploy:
    needs: test
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.KEY }}
        script: |
          cd /var/www/citruscms
          git pull origin main
          docker-compose -f docker-compose.prod.yml down
          docker-compose -f docker-compose.prod.yml up -d --build
          docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
          docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
          docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
```

## 📊 Monitoring ve Analytics

### 1. Laravel Telescope
```php
// config/telescope.php
return [
    'enabled' => env('TELESCOPE_ENABLED', false),
    
    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'chunk' => 1000,
        ],
    ],
    
    'entries' => [
        'requests' => [
            'enabled' => env('TELESCOPE_REQUESTS_ENABLED', true),
            'slow' => env('TELESCOPE_REQUESTS_SLOW', 1000),
        ],
        
        'commands' => [
            'enabled' => env('TELESCOPE_COMMANDS_ENABLED', true),
        ],
        
        'schedule' => [
            'enabled' => env('TELESCOPE_SCHEDULE_ENABLED', true),
        ],
        
        'jobs' => [
            'enabled' => env('TELESCOPE_JOBS_ENABLED', true),
        ],
        
        'exceptions' => [
            'enabled' => env('TELESCOPE_EXCEPTIONS_ENABLED', true),
        ],
        
        'logs' => [
            'enabled' => env('TELESCOPE_LOGS_ENABLED', true),
        ],
        
        'notifications' => [
            'enabled' => env('TELESCOPE_NOTIFICATIONS_ENABLED', true),
        ],
        
        'queries' => [
            'enabled' => env('TELESCOPE_QUERIES_ENABLED', true),
            'slow' => env('TELESCOPE_QUERIES_SLOW', 100),
        ],
        
        'redis' => [
            'enabled' => env('TELESCOPE_REDIS_ENABLED', true),
        ],
    ],
];
```

### 2. Performance Monitoring
```php
// app/Http/Middleware/PerformanceMonitor.php
class PerformanceMonitor
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        if ($executionTime > 1000) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime
            ]);
        }
        
        return $response;
    }
}
```

Bu geliştirme iş akışı, CitrusCMS'in başarılı bir şekilde geliştirilmesi için gerekli tüm aşamaları ve teknik detayları içermektedir. Her aşama SOLID prensiplerine uygun olarak tasarlanmış ve modern geliştirme pratiklerini takip etmektedir. 