<?php
/**
* @package     PriorityAPI
* @author      Ante Laca <ante.laca@gmail.com>
* @copyright   2018 Roi Holdings
*/
namespace PriorityAPI;


class API_List extends \WP_List_Table
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function prepare_items()
    {

        $columns   = $this->get_columns();
        $hidden    = $this->get_hidden_columns();
        $sortable  = $this->get_sortable_columns();

        $data = $GLOBALS['wpdb']->get_results('SELECT * FROM ' . $GLOBALS['wpdb']->prefix . 'p18a_logs', ARRAY_A);
        
        usort($data, [$this, 'sort_data']);

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args([
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ]);

        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

        $this->_column_headers = [$columns, $hidden, $sortable];
        $this->items = $data;
    
    }

    public function get_columns()
    {
        $columns = [
            'timestamp'      => __('Timestamp', 'p18a'),
            'url'            => __('URL', 'p18a'),
            'request_method' => __('Request Method', 'p18a'),
            'json_request'   => __('JSON Request', 'p18a'),
            'json_response'  => __('JSON Response', 'p18a'),
            'json_status'    => __('Status', 'p18a'),
            'repost'         => __('RePost JSON', 'p18a')
        ];

        return $columns;
    }

    public function get_hidden_columns()
    {
        return [];
    }

    public function get_sortable_columns()
    {
        return [
            'timestamp' => ['timestamp', true]
        ];
    }

    public function column_default($item, $name)
    {
        switch($name) {
            case 'timestamp':
                    return date('d/m/Y H:i:s', strtotime($item[$name]));
                break;
            case 'json_request':
            case 'json_response':

                if(empty($item[$name])) return '';

                return '
                    <a href="#" class="p18a-thickbox" data-type="' . $name . '" data-id="' . $item['id'] . '">' . __('View', 'p18a') . '</a>
                    <div class="p18a-data" data-' . $name . '="' . $item['id'] . '">' . $item[$name] . '</div>
                ';

                break;
            case 'url':
            case 'request_method':
                return $item[$name];
                break;
            case 'json_status':

          #  return $item[$name];

                $status = ($item[$name]) ? __('Success', 'p18a') : __('Failed', 'p18a');

                return '<div class="p18a-status-' . $item[$name] . '">' . $status . '</div>';

                break;
            case 'repost':
                return '<a class="button" href="' . wp_nonce_url(admin_url('admin.php?page=' . P18A_PLUGIN_ADMIN_URL . '&tab=transaction-log&repost=' . $item['id']), 'repost', 'request'). '">' . __('RePost', 'p18a') . '</a>';
        }
    }


    private function sort_data($a, $b)
    {
        $order   = 'desc';
        $orderby = 'timestamp';

        if ( ! empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        if ( ! empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }
    
}
