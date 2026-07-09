<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductAttributeMasterValueModel;
use App\Models\ProductAttributeModel;

class ProductAttributeController extends BaseController
{
    protected $attributeModel;
    protected $attributeMasterValueModel;

    public function __construct()
    {
        $this->attributeModel = new ProductAttributeModel();
        $this->attributeMasterValueModel = new ProductAttributeMasterValueModel();
    }

    public function webIndex()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = 10;

        $search = $this->request->getVar('search');
        $categoryId = $this->request->getVar('category_id');

        $builder = $this->attributeModel;

        if (!empty($search)) {
            $builder->like('attribute_name', $search);
        }

        if (!empty($categoryId)) {
            $builder->where('category_id', $categoryId);
        }

        $attributes = $builder
            ->orderBy('sort_order', 'ASC')
            ->paginate($perPage, 'default', $page);

        // Ambil semua master values
        $masterValues = $this->attributeMasterValueModel
            ->select('attribute_id, value')
            ->findAll();

        // Group by attribute_id
        $groupedValues = [];
        foreach ($masterValues as $row) {
            $groupedValues[$row['attribute_id']][] = $row['value'];
        }

        // Get all categories for lookup
        $categoryModel = new \App\Models\ProductCategoryModel();
        $categories = $categoryModel->findAll();
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['category_id']] = $cat['category_name'];
        }

        // Gabungkan value pakai koma dan add category name
        foreach ($attributes as &$attr) {
            $values = $groupedValues[$attr['attribute_id']] ?? [];
            $attr['master_values'] = implode(', ', $values);
            $attr['category_name'] = $categoryMap[$attr['category_id']] ?? 'All';
        }
        unset($attr);

        $pager = [
            'currentPage' => $this->attributeModel->pager->getCurrentPage('default'),
            'totalPages' => $this->attributeModel->pager->getPageCount('default'),
            'limit' => $perPage
        ];

        return view('product_attribute/v_index', [
            'attributes' => $attributes,
            'categories' => $categories,
            'search' => $search,
            'selectedCategoryId' => $categoryId,
            'pager' => $pager
        ]);
    }

    public function form()
    {
        $id = $this->request->getVar('id');
        $data = [];

        // Fetch all categories for dropdown
        $categoryModel = new \App\Models\ProductCategoryModel();
        $data['categories'] = $categoryModel->findAll();

        if ($id) {
            $attribute = $this->attributeModel->find($id);

            if (!$attribute) {
                return redirect()->to('/product-attribute')->with('failed', 'Attribute not found.');
            }

            // Ambil master values
            $options = $this->attributeMasterValueModel
                ->where('attribute_id', $id)
                ->findAll();

            $data['attribute'] = $attribute;
            $data['options'] = $options;
        }

        return view('product_attribute/v_form', $data);
    }

    public function save()
    {
        $id = $this->request->getVar('id');
        $rules = $this->attributeModel->validationRules;

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMessage = implode('<br>', $errors);

            return redirect()->back()->withInput()->with('failed', $errorMessage);
        }

        $data = [
            'attribute_name' => $this->request->getPost('attribute_name'),
            'attribute_type' => $this->request->getPost('attribute_type'),
            'category_id' => $this->request->getPost('category_id') ?: null,
            'is_variantable' => $this->request->getPost('is_variantable') ? 1 : 0,
            'is_required' => $this->request->getPost('is_required') ? 1 : 0,
            'is_filterable' => $this->request->getPost('is_filterable') ? 1 : 0,
            'use_master_values' => $this->request->getPost('use_master_values') ? 1 : 0,
            'sort_order' => (int)$this->request->getPost('sort_order') ?? 0,
        ];

        // Paksa use_master_values = 1 jika tipe atribut adalah dropdown
        if ($data['attribute_type'] === 'dropdown') {
            $data['use_master_values'] = 1;
        }

        if ($id) {

            $this->attributeModel->update($id, $data);

            if ($data['attribute_type'] === 'dropdown') {

                $valueIds = $this->request->getPost('value_ids'); // array id lama / kosong
                $values   = $this->request->getPost('values');    // array value

                $existing = $this->attributeMasterValueModel
                    ->where('attribute_id', $id)
                    ->findAll();

                // Ambil ID yang masih dipakai
                $stillUsedIds = [];

                foreach ($values as $index => $val) {
                    if (trim($val) === '') continue;

                    $valueId = $valueIds[$index];

                    if ($valueId) {
                        // UPDATE data lama
                        $this->attributeMasterValueModel->update($valueId, [
                            'value' => $val
                        ]);

                        $stillUsedIds[] = $valueId;
                    } else {
                        // INSERT baru
                        $newId = $this->attributeMasterValueModel->insert([
                            'attribute_id' => $id,
                            'value' => $val,
                        ]);

                        $stillUsedIds[] = $newId;
                    }
                }

                // DELETE yang tidak dipakai
                if (!empty($existing)) {
                    $existingIds = array_column($existing, 'attribute_master_id');

                    $toDelete = array_diff($existingIds, $stillUsedIds);

                    if (!empty($toDelete)) {
                        $this->attributeMasterValueModel
                            ->whereIn('attribute_master_id', $toDelete)
                            ->delete();
                    }
                }
            }

            return redirect()->to('/product-attribute')->with('success', 'Attribute updated successfully!');
        }


        $this->attributeModel->insert([
            'attribute_name' => $data['attribute_name'],
            'attribute_type' => $data['attribute_type'],
            'category_id' => $data['category_id'],
            'is_variantable' => $data['is_variantable'],
            'is_required' => $data['is_required'],
            'is_filterable' => $data['is_filterable'],
            'use_master_values' => $data['use_master_values'],
            'sort_order' => $data['sort_order'],
        ]);
        $attributeId = $this->attributeModel->getInsertID();

        // insert dropdown values
        if ($data['attribute_type'] === 'dropdown') {
            $values = $this->request->getPost('values');

            if (!empty($values)) {
                foreach ($values as $val) {
                    if (trim($val) === '') continue;

                    $this->attributeMasterValueModel->insert([
                        'attribute_id' => $attributeId,
                        'value' => $val,
                    ]);
                }
            }
        }

        return redirect()->to('/product-attribute')->with('success', 'Attribute created successfully!');
    }

    public function webDelete($id)
    {
        $this->attributeModel->delete($id);
        return redirect()->to('/product-attribute')->with('success', 'Product attribute deleted successfully.');
    }
}
