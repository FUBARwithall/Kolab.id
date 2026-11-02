<?php

namespace App\Controllers;

use App\Models\ArticleModel;
use App\Models\ArticleItem;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Articles extends BaseController
{
    protected ArticleModel $articleModel;

    public function __construct()
    {
        $this->articleModel = new ArticleModel();
        $this->request      = service('request');
        $this->response     = service('response');
        helper(['url', 'form', 'text', 'filesystem']);
    }

    /**
     * Get Current Authenticated User ID
     */
    protected function currentAuthorId(): int
    {
        $session = session();
        return (int) ($session->get('id') ?? $session->get('user_id') ?? 1);
    }

    /**
     * Get Current Authenticated User Name
     */
    protected function currentAuthorName(): string
    {
        $session = session();
        $name = $session->get('full_name') ?? $session->get('name') ?? $session->get('username');
        return !empty($name) ? (string) $name : 'Admin';
    }

    /**
     * Check if request is AJAX or expects JSON
     */
    protected function isJsonOrAjax(): bool
    {
        if (method_exists($this->request, 'isAJAX') && $this->request->isAJAX()) {
            return true;
        }
        if (method_exists($this->request, 'getHeaderLine') && str_contains($this->request->getHeaderLine('accept'), 'json')) {
            return true;
        }
        $post = $this->request->getPost() ?: ($_POST ?? []);
        if (!empty($post['ajax']) || !empty($_POST['ajax']) || $this->request->getGet('format') === 'json') {
            return true;
        }
        return false;
    }

    /**
     * Halaman Utama CMS / Daftar Artikel (CI4 & CI3 Parity)
     */
    public function index($offset = 0)
    {
        $q        = trim((string) ($this->request->getGet('q') ?? ''));
        $field    = trim((string) ($this->request->getGet('f') ?? ''));
        $status   = trim((string) ($this->request->getGet('status') ?? ''));
        $category = trim((string) ($this->request->getGet('category') ?? ''));
        $limit    = 10;
        $offset   = (int) $offset;

        $articles   = $this->articleModel->get($q, $field, $limit, $offset, $status, $category);
        $totalCount = $this->articleModel->count_all($q, $field, $status, $category);
        $categories = $this->articleModel->categories();

        $pager = service('pager');
        $paginationHtml = $pager->makeLinks(
            ($offset / $limit) + 1,
            $limit,
            $totalCount,
            'default_full',
            0,
            'default',
            site_url('articles/index')
        );

        $data = [
            'title'           => 'Artikel',
            'controller'      => 'Articles',
            'articles'        => $articles,
            'articles_counts' => $totalCount,
            'categories'      => $categories,
            'pagination'      => $paginationHtml,
            'q'               => $q,
            'f'               => $field,
            'status'          => $status,
            'category'        => $category,
            'offset'          => $offset,
            'limit'           => $limit,
        ];

        return view('articles/index', $data);
    }

    /**
     * DataTables Server-Side Endpoint
     */
    public function getDatatable(): ResponseInterface
    {
        $draw   = (int) ($this->request->getPost('draw') ?? ($_POST['draw'] ?? 1));
        $start  = (int) ($this->request->getPost('start') ?? ($_POST['start'] ?? 0));
        $length = (int) ($this->request->getPost('length') ?? ($_POST['length'] ?? 10));
        
        $searchParam = $this->request->getPost('search') ?? ($_POST['search'] ?? null);
        $search = is_array($searchParam) ? (string) ($searchParam['value'] ?? '') : (string) $searchParam;

        $orderParam = $this->request->getPost('order') ?? ($_POST['order'] ?? null);
        $orderColIdx = is_array($orderParam) ? (int) ($orderParam[0]['column'] ?? 0) : 0;
        $orderDir    = is_array($orderParam) ? strtoupper((string) ($orderParam[0]['dir'] ?? 'DESC')) : 'DESC';

        $status   = $this->request->getPost('status') ?? $this->request->getGet('status') ?? ($_POST['status'] ?? null);
        $category = $this->request->getPost('category') ?? $this->request->getGet('category') ?? ($_POST['category'] ?? null);

        $columns = [
            0 => 'articles.id',
            1 => 'articles.cover_image',
            2 => 'articles.title',
            3 => 'articles.category',
            4 => 'articles.author_name',
            5 => 'articles.status',
            6 => 'articles.published_at',
            7 => 'articles.views',
            8 => 'articles.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'articles.id';

        $result = $this->articleModel->getDatatables($start, $length, $search, $orderCol, $orderDir, $status, $category);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $id = (int) $row['id'];
            $st = strtoupper((string) ($row['status'] ?? 'DRAFT'));

            // Status badge
            if ($st === 'PUBLISHED') {
                $statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>PUBLISHED</span>';
            } elseif ($st === 'ARCHIVED') {
                $statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fas fa-archive me-1"></i>ARCHIVED</span>';
            } else {
                $statusBadge = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="fas fa-file-alt me-1"></i>DRAFT</span>';
            }

            // Cover thumbnail
            $cover = $row['cover_image'] ?? $row['featured_image'] ?? '';
            $coverHtml = '<div class="d-flex align-items-center justify-content-center bg-light text-muted border rounded" style="width:56px; height:38px;"><i class="fas fa-image"></i></div>';
            if (!empty($cover)) {
                $coverUrl = (strpos($cover, 'http://') === 0 || strpos($cover, 'https://') === 0)
                    ? $cover
                    : base_url('uploads/articles/' . basename($cover));
                $coverHtml = '<img src="' . esc($coverUrl) . '" alt="" class="rounded border" style="width:56px; height:38px; object-fit:cover;">';
            }

            // Action buttons
            $actionBtn = '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . site_url('articles/view/' . $id) . '" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail"><i class="fas fa-eye"></i></a>
                    <a href="' . site_url('articles/edit/' . $id) . '" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit"><i class="fas fa-pencil-alt"></i></a>
            ';
            if ($st !== 'PUBLISHED') {
                $actionBtn .= '<button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2 js-status" data-id="' . $id . '" data-action="publish" title="Publish"><i class="fas fa-check"></i></button>';
            } else {
                $actionBtn .= '<button type="button" class="btn btn-outline-warning btn-sm rounded-2 py-1 px-2 js-status" data-id="' . $id . '" data-action="unpublish" title="Unpublish"><i class="fas fa-pause"></i></button>';
            }
            if ($st !== 'ARCHIVED') {
                $actionBtn .= '<button type="button" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2 js-status" data-id="' . $id . '" data-action="archive" title="Archive"><i class="fas fa-archive"></i></button>';
            }
            $actionBtn .= '
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2 remove-data" data-id="' . $id . '" data-href="' . site_url('articles/delete/' . $id) . '" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                </div>
            ';

            $checkbox = '<input type="checkbox" class="form-check-input check-item" name="id[]" value="' . $id . '">';

            $titleHtml = '
                <div class="fw-bold text-dark">' . esc($row['title']) . '</div>
                <div class="small text-muted font-monospace"><a href="' . base_url('artikel/' . esc($row['slug'])) . '" target="_blank" class="text-decoration-none text-muted">/artikel/' . esc($row['slug']) . '</a></div>
            ';
            if (!empty($row['is_featured'])) {
                $titleHtml .= '<span class="badge bg-info-subtle text-info border border-info-subtle mt-1" style="font-size:10px;"><i class="fas fa-star me-1"></i>Featured</span>';
            }

            $catHtml = !empty($row['category'])
                ? '<span class="badge bg-light text-dark border">' . esc($row['category']) . '</span>'
                : '<span class="text-muted">-</span>';

            $formattedData[] = [
                'checkbox'    => $checkbox,
                'id'          => $id,
                'cover'       => $coverHtml,
                'title'       => $titleHtml,
                'category'    => $catHtml,
                'author_name' => esc($row['author_name'] ?? 'Admin'),
                'status'      => $statusBadge,
                'published'   => !empty($row['published_at']) ? date('d M Y H:i', strtotime($row['published_at'])) : '<span class="text-muted fst-italic">-</span>',
                'views'       => '<span class="badge bg-light text-dark border"><i class="fas fa-eye me-1"></i>' . ((int) ($row['views'] ?? 0)) . '</span>',
                'action'      => $actionBtn,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data'            => $formattedData,
        ]);
    }

    /**
     * Get single article JSON
     */
    public function getOne($id = null): ResponseInterface
    {
        $id = (int) $id;
        $article = $this->articleModel->find($id);

        if (!$article) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Artikel tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $article,
        ]);
    }

    /**
     * Form Tulis Artikel Baru
     */
    public function add()
    {
        $data = [
            'title'      => 'Artikel Baru',
            'controller' => 'Articles',
            'categories' => $this->articleModel->categories(),
        ];

        return view('articles/add', $data);
    }

    /**
     * Simpan Artikel Baru
     */
    public function add_save()
    {
        $post = $this->request->getPost() ?: ($_POST ?? []);

        $title   = trim((string) ($post['title'] ?? ''));
        $content = (string) ($post['content'] ?? '');

        if ($title === '' || $content === '') {
            $msg = 'Judul dan konten artikel wajib diisi.';
            if ($this->isJsonOrAjax()) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            session()->setFlashdata('error', $msg);
            return redirect()->back()->withInput();
        }

        if (mb_strlen($title) > 200) {
            $msg = 'Judul artikel maksimal 200 karakter.';
            if ($this->isJsonOrAjax()) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            session()->setFlashdata('error', $msg);
            return redirect()->back()->withInput();
        }

        $category = $this->normalizeCategory($post['category'] ?? null);
        $tags     = $this->normalizeTags($post['tags'] ?? '');
        $status   = $this->normalizeStatus($post['status'] ?? 'DRAFT');
        $slug     = $this->uniqueSlug($title, $post['slug'] ?? '');
        $excerpt  = $this->buildExcerpt($post['excerpt'] ?? '', $content);

        $saveData = [
            'title'            => $title,
            'slug'             => $slug,
            'excerpt'          => $excerpt,
            'content'          => $content,
            'category'         => $category,
            'tags'             => $tags,
            'author_id'        => $this->currentAuthorId(),
            'author_name'      => $this->currentAuthorName(),
            'status'           => $status,
            'is_featured'      => !empty($post['is_featured']) ? 1 : 0,
            'views'            => 0,
            'meta_title'       => trim((string) ($post['meta_title'] ?? '')) ?: null,
            'meta_description' => trim((string) ($post['meta_description'] ?? '')) ?: null,
            'published_at'     => ($status === 'PUBLISHED') ? date('Y-m-d H:i:s') : null,
        ];

        // Process cover image (from FineUploader tmp or standard multipart)
        $cover = $this->saveCoverFromTmp($post) ?: $this->handleMultipartCoverUpload();
        if ($cover) {
            $saveData['cover_image']    = $cover;
            $saveData['featured_image'] = $cover;
        }

        try {
            $id = $this->articleModel->insert($saveData, true);

            if (!$id) {
                $msg = 'Gagal menyimpan artikel ke basis data.';
                if ($this->isJsonOrAjax()) {
                    return $this->response->setJSON(['success' => false, 'message' => $msg]);
                }
                session()->setFlashdata('error', $msg);
                return redirect()->back()->withInput();
            }

            $saveType = (string) ($post['save_type'] ?? 'back');

            if ($this->isJsonOrAjax()) {
                if ($saveType === 'stay') {
                    return $this->response->setJSON([
                        'success'  => true,
                        'id'       => $id,
                        'message'  => 'Artikel berhasil disimpan!',
                        'redirect' => site_url('articles/edit/' . $id),
                    ]);
                }
                return $this->response->setJSON([
                    'success'  => true,
                    'id'       => $id,
                    'message'  => 'Artikel berhasil disimpan!',
                    'redirect' => site_url('articles'),
                ]);
            }

            session()->setFlashdata('success', 'Artikel berhasil disimpan!');
            if ($saveType === 'stay') {
                return redirect()->to(site_url('articles/edit/' . $id));
            }
            return redirect()->to(site_url('articles'));

        } catch (\Throwable $e) {
            log_message('error', 'Articles add_save failed: ' . $e->getMessage());
            $msg = 'Terjadi kesalahan sistem saat menyimpan artikel: ' . $e->getMessage();
            if ($this->isJsonOrAjax()) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            session()->setFlashdata('error', $msg);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Form Edit Artikel
     */
    public function edit($id = null)
    {
        $id = (int) $id;
        $article = $this->articleModel->findItem($id);

        if (!$article) {
            throw PageNotFoundException::forPageNotFound('Artikel tidak ditemukan.');
        }

        $data = [
            'title'      => 'Edit Artikel',
            'controller' => 'Articles',
            'article'    => $article,
            'categories' => $this->articleModel->categories(),
        ];

        return view('articles/edit', $data);
    }

    /**
     * Simpan Perubahan Artikel
     */
    public function edit_save($id = null)
    {
        $id = (int) ($id ?? $this->request->getPost('id') ?? ($_POST['id'] ?? 0));
        $article = $this->articleModel->findItem($id);

        if (!$article) {
            $msg = 'Artikel tidak ditemukan.';
            if ($this->isJsonOrAjax()) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            session()->setFlashdata('error', $msg);
            return redirect()->to(site_url('articles'));
        }

        $post = $this->request->getPost() ?: ($_POST ?? []);

        $title   = trim((string) ($post['title'] ?? ''));
        $content = (string) ($post['content'] ?? '');

        if ($title === '' || $content === '') {
            $msg = 'Judul dan konten artikel wajib diisi.';
            if ($this->isJsonOrAjax()) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            session()->setFlashdata('error', $msg);
            return redirect()->back()->withInput();
        }

        $category = $this->normalizeCategory($post['category'] ?? null);
        $tags     = $this->normalizeTags($post['tags'] ?? '');
        $status   = $this->normalizeStatus($post['status'] ?? $article->status);
        $slug     = $this->uniqueSlug($title, $post['slug'] ?? '', $id);
        $excerpt  = $this->buildExcerpt($post['excerpt'] ?? '', $content);

        $saveData = [
            'title'            => $title,
            'slug'             => $slug,
            'excerpt'          => $excerpt,
            'content'          => $content,
            'category'         => $category,
            'tags'             => $tags,
            'status'           => $status,
            'is_featured'      => !empty($post['is_featured']) ? 1 : 0,
            'meta_title'       => trim((string) ($post['meta_title'] ?? '')) ?: null,
            'meta_description' => trim((string) ($post['meta_description'] ?? '')) ?: null,
        ];

        if ($status === 'PUBLISHED' && empty($article->published_at)) {
            $saveData['published_at'] = date('Y-m-d H:i:s');
        }

        // Handle cover removal request
        if (!empty($post['remove_cover']) && $post['remove_cover'] === '1') {
            $this->deleteCoverFileByName($article->cover_image);
            $saveData['cover_image']    = null;
            $saveData['featured_image'] = null;
        }

        // Process new cover image (FineUploader tmp or standard multipart)
        $newCover = $this->saveCoverFromTmp($post) ?: $this->handleMultipartCoverUpload();
        if ($newCover) {
            $this->deleteCoverFileByName($article->cover_image);
            $saveData['cover_image']    = $newCover;
            $saveData['featured_image'] = $newCover;
        }

        try {
            $this->articleModel->update($id, $saveData);

            $saveType = (string) ($post['save_type'] ?? 'stay');

            if ($this->isJsonOrAjax()) {
                if ($saveType === 'stay') {
                    return $this->response->setJSON([
                        'success'  => true,
                        'id'       => $id,
                        'message'  => 'Artikel berhasil diperbarui!',
                        'redirect' => site_url('articles/edit/' . $id),
                    ]);
                }
                return $this->response->setJSON([
                    'success'  => true,
                    'id'       => $id,
                    'message'  => 'Artikel berhasil diperbarui!',
                    'redirect' => site_url('articles'),
                ]);
            }

            session()->setFlashdata('success', 'Artikel berhasil diperbarui!');
            if ($saveType === 'stay') {
                return redirect()->to(site_url('articles/edit/' . $id));
            }
            return redirect()->to(site_url('articles'));

        } catch (\Throwable $e) {
            log_message('error', 'Articles edit_save failed: ' . $e->getMessage());
            $msg = 'Terjadi kesalahan sistem saat memperbarui artikel: ' . $e->getMessage();
            if ($this->isJsonOrAjax()) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            session()->setFlashdata('error', $msg);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Detail Preview Artikel
     */
    public function view($id = null)
    {
        $id = (int) $id;
        $article = $this->articleModel->findItem($id);

        if (!$article) {
            throw PageNotFoundException::forPageNotFound('Artikel tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Artikel: ' . $article->title,
            'controller' => 'Articles',
            'article'    => $article,
        ];

        return view('articles/view', $data);
    }

    /**
     * Hapus Artikel (Single & Bulk Delete Support)
     */
    public function delete($id = null)
    {
        $ids = [];
        if (!empty($id)) {
            $ids[] = (int) $id;
        }

        $getIds  = $this->request->getGet('id');
        $postIds = $this->request->getPost('id') ?? ($_POST['id'] ?? null);

        if (!empty($getIds)) {
            $ids = array_merge($ids, is_array($getIds) ? $getIds : [$getIds]);
        }
        if (!empty($postIds)) {
            $ids = array_merge($ids, is_array($postIds) ? $postIds : [$postIds]);
        }

        $ids = array_filter(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            $msg = 'Tidak ada artikel yang dipilih untuk dihapus.';
            if ($this->isJsonOrAjax()) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            session()->setFlashdata('error', $msg);
            return redirect()->to(site_url('articles'));
        }

        $deletedCount = 0;
        foreach ($ids as $articleId) {
            $article = $this->articleModel->findItem($articleId);
            if ($article) {
                $this->deleteCoverFileByName($article->cover_image);
                $this->articleModel->delete($articleId);
                $deletedCount++;
            }
        }

        $msg = "{$deletedCount} artikel berhasil dihapus.";
        if ($this->isJsonOrAjax()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg, 'deleted' => $deletedCount]);
        }

        session()->setFlashdata('success', $msg);
        return redirect()->to(site_url('articles'));
    }

    /**
     * Quick status transition: Publish
     */
    public function publish($id = null): ResponseInterface
    {
        return $this->changeStatus((int) $id, 'PUBLISHED');
    }

    /**
     * Quick status transition: Unpublish / Draft
     */
    public function unpublish($id = null): ResponseInterface
    {
        return $this->changeStatus((int) $id, 'DRAFT');
    }

    /**
     * Quick status transition: Archive
     */
    public function archive($id = null): ResponseInterface
    {
        return $this->changeStatus((int) $id, 'ARCHIVED');
    }

    /**
     * Internal status changer returning JSON
     */
    protected function changeStatus(int $id, string $status): ResponseInterface
    {
        $article = $this->articleModel->findItem($id);
        if (!$article) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Artikel tidak ditemukan.',
            ]);
        }

        $update = ['status' => $status];
        if ($status === 'PUBLISHED' && empty($article->published_at)) {
            $update['published_at'] = date('Y-m-d H:i:s');
        }

        $this->articleModel->update($id, $update);

        return $this->response->setJSON([
            'success' => true,
            'status'  => $status,
            'message' => "Status artikel berhasil diubah menjadi {$status}.",
        ]);
    }

    /**
     * FineUploader / AJAX Upload Handler for Cover Image
     */
    public function upload_cover_file(): ResponseInterface
    {
        $file = $this->request->getFile('qqfile') ?? $this->request->getFile('file');
        $uuid = $this->request->getPost('qquuid') ?? ($_POST['qquuid'] ?? uniqid());

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $tmpDir = FCPATH . 'uploads/tmp/' . $uuid;
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0777, true);
            }

            $origName = $file->getClientName();
            $file->move($tmpDir, $origName);

            return $this->response->setJSON([
                'success'    => true,
                'uploadName' => $origName,
                'uuid'       => $uuid,
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'error'   => 'Gagal mengunggah file cover.',
        ]);
    }

    /**
     * FineUploader Delete Handler for Cover Image
     */
    public function delete_cover_file($uuid = null): ResponseInterface
    {
        $uuid = $uuid ?: ($this->request->getPost('qquuid') ?? $this->request->getGet('qquuid') ?? ($_POST['qquuid'] ?? ''));

        if ($this->request->getGet('by') === 'id') {
            $id = (int) $uuid;
            $article = $this->articleModel->findItem($id);
            if ($article) {
                $this->deleteCoverFileByName($article->cover_image);
                $this->articleModel->update($id, ['cover_image' => null, 'featured_image' => null]);
            }
            return $this->response->setJSON(['success' => true]);
        }

        if (!empty($uuid)) {
            $tmpDir = FCPATH . 'uploads/tmp/' . basename($uuid);
            if (is_dir($tmpDir)) {
                $files = glob($tmpDir . '/*');
                foreach ($files as $f) {
                    if (is_file($f)) @unlink($f);
                }
                @rmdir($tmpDir);
            }
        }

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Get Cover File JSON Info for FineUploader Previews
     */
    public function get_cover_file($id = null): ResponseInterface
    {
        $id = (int) $id;
        $article = $this->articleModel->findItem($id);
        $files = [];

        if ($article && !empty($article->cover_image)) {
            $path = FCPATH . 'uploads/articles/' . basename($article->cover_image);
            $size = is_file($path) ? filesize($path) : 0;
            $files[] = [
                'success'      => true,
                'uuid'         => (string) $id,
                'name'         => basename($article->cover_image),
                'size'         => $size,
                'thumbnailUrl' => base_url('uploads/articles/' . basename($article->cover_image)),
            ];
        }

        return $this->response->setJSON($files);
    }

    /**
     * Export Articles to CSV (UTF-8 BOM)
     */
    public function export()
    {
        $q        = $this->request->getGet('q');
        $field    = $this->request->getGet('f');
        $status   = $this->request->getGet('status');
        $category = $this->request->getGet('category');

        $articles = $this->articleModel->get($q, $field, 0, 0, $status, $category);
        $filename = 'Export_Artikel_' . date('Ymd_His') . '.csv';

        $output = fopen('php://temp', 'r+');
        // UTF-8 BOM
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'ID',
            'Judul Artikel',
            'Slug URL',
            'Kategori',
            'Tag',
            'Penulis',
            'Status',
            'Featured',
            'Views',
            'Meta Title',
            'Meta Description',
            'Tanggal Terbit',
            'Tanggal Dibuat',
        ]);

        foreach ($articles as $a) {
            fputcsv($output, [
                $a->id,
                $a->title,
                $a->slug,
                $a->category ?: '-',
                $a->tags ?: '-',
                $a->author_name ?: 'Admin',
                $a->status,
                $a->is_featured ? 'Ya' : 'Tidak',
                (int) $a->views,
                $a->meta_title ?: '-',
                $a->meta_description ?: '-',
                $a->published_at ?: '-',
                $a->created_at ?: '-',
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csvContent);
    }

    /**
     * Printable Rekapitulasi Artikel
     */
    public function export_pdf()
    {
        $q        = $this->request->getGet('q');
        $field    = $this->request->getGet('f');
        $status   = $this->request->getGet('status');
        $category = $this->request->getGet('category');

        $articles = $this->articleModel->get($q, $field, 0, 0, $status, $category);

        $data = [
            'title'    => 'Rekapitulasi Daftar Artikel',
            'articles' => $articles,
        ];

        return view('articles/print_all', $data);
    }

    /**
     * Printable Single Article Preview / PDF
     */
    public function single_pdf($id = null)
    {
        $id = (int) $id;
        $article = $this->articleModel->findItem($id);

        if (!$article) {
            throw PageNotFoundException::forPageNotFound('Artikel tidak ditemukan.');
        }

        $data = [
            'title'   => 'Cetak Artikel: ' . $article->title,
            'article' => $article,
        ];

        return view('articles/print_single', $data);
    }

    /**
     * Public Reader / Detail Route
     */
    public function read($slug = null)
    {
        if (empty($slug)) {
            return redirect()->to(site_url('articles'));
        }

        $article = $this->articleModel->getBySlug($slug);
        if (!$article) {
            throw PageNotFoundException::forPageNotFound('Artikel tidak ditemukan.');
        }

        $this->articleModel->incrementViews((int) $article['id']);
        $article['views'] = ((int) ($article['views'] ?? 0)) + 1;

        $data = [
            'title'   => $article['title'],
            'article' => $this->articleModel->wrapItem($article),
        ];

        return view('articles/view', $data);
    }

    /**
     * Delegated Feed Endpoint
     */
    public function feed(?string $slug = null): ResponseInterface
    {
        $api = new ArticlesApi();
        return $api->feed($slug);
    }

    // ==========================================
    // Internal Helper Methods
    // ==========================================

    /**
     * Generate unique URL slug
     */
    protected function uniqueSlug(string $title, ?string $slug = '', $ignoreId = null): string
    {
        $base = trim((string) $slug);
        if ($base === '') {
            $base = $title;
        }

        $base = url_title($base, '-', true);
        if ($base === '') {
            $base = 'artikel';
        }

        $candidate = $base;
        $suffix    = 2;

        while ($this->articleModel->slug_exists($candidate, $ignoreId)) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    /**
     * Normalize category input (supports comma string or array of tags)
     */
    protected function normalizeCategory($raw): ?string
    {
        if (is_array($raw)) {
            $clean = array_filter(array_map('trim', $raw));
            return !empty($clean) ? implode(', ', $clean) : null;
        }

        $trimmed = trim((string) $raw);
        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * Normalize tags input
     */
    protected function normalizeTags(?string $tags): ?string
    {
        if (empty($tags)) {
            return null;
        }

        $list = array_filter(array_map('trim', explode(',', $tags)));
        return !empty($list) ? implode(', ', $list) : null;
    }

    /**
     * Normalize article status
     */
    protected function normalizeStatus(?string $status): string
    {
        $status = strtoupper(trim((string) $status));
        return in_array($status, ['DRAFT', 'PUBLISHED', 'ARCHIVED'], true) ? $status : 'DRAFT';
    }

    /**
     * Build auto excerpt if empty
     */
    protected function buildExcerpt(?string $excerpt, string $content): string
    {
        $trimmed = trim((string) $excerpt);
        if ($trimmed !== '') {
            return mb_substr($trimmed, 0, 500);
        }

        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
        return mb_substr($plain, 0, 220);
    }

    /**
     * Move cover image from FineUploader temporary storage to public uploads
     */
    protected function saveCoverFromTmp(array $post): ?string
    {
        $name = $post['cover_image_name'] ?? ($_POST['cover_image_name'] ?? null);
        $uuid = $post['cover_image_uuid'] ?? ($_POST['cover_image_uuid'] ?? null);

        if (empty($name) || empty($uuid)) {
            return null;
        }

        $name = basename($name);
        $uuid = basename($uuid);
        $tmp  = FCPATH . 'uploads/tmp/' . $uuid . '/' . $name;

        if (!is_file($tmp)) {
            return null;
        }

        $uploadDir = FCPATH . 'uploads/articles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return null;
        }

        $base     = pathinfo($name, PATHINFO_FILENAME);
        $base     = preg_replace('/[^A-Za-z0-9_-]+/', '-', $base);
        $base     = trim($base, '-') ?: 'cover';
        $filename = date('YmdHis') . '-' . substr($base, 0, 60) . '.' . $extension;

        if (!rename($tmp, $uploadDir . $filename)) {
            return null;
        }

        return $filename;
    }

    /**
     * Handle standard multipart file upload for cover image
     */
    protected function handleMultipartCoverUpload(): ?string
    {
        $file = $this->request->getFile('cover_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/articles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                return null;
            }

            $newName = date('YmdHis') . '-' . $file->getRandomName();
            $file->move($uploadDir, $newName);
            return $newName;
        }
        return null;
    }

    /**
     * Delete cover file from disk
     */
    protected function deleteCoverFileByName(?string $filename): void
    {
        if (empty($filename)) {
            return;
        }

        $path = FCPATH . 'uploads/articles/' . basename($filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
