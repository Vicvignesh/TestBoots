<?php
//Collection query:

// mysqldump -u praveen9_mag2 -p praveen9_magento2 > praveen9_magento2_backup.sql


//get all items:

$collection = $this->collectionFactory->create();
$collection->getItems();

//set data:

$customModel = $this->customFactory->create();
$customModel->setName('Sample Name');

//filter by use collection:
$collection = $this->collectionFactory->create()->addFieldToFilter('entity_id', $id); // Filter by ID


//filter by multiplae values:
$collection = $this->collectionFactory->create()->addFieldToFilter('entity_id', ['in' => [1, 2, 3]]);

//filter by greaterthan values:
$collection = $this->collectionFactory->create()
->addFieldToFilter('entity_id', ['gteq' => 10]); // ID greater than or equal to 10

 $collection->addFieldToFilter('price', ['gt' => 100]); // ID greater than

 $collection->addFieldToFilter('price', ['lt' => 100]); // id less than

 $collection->addFieldToFilter('price', ['lteq' => 100]); // id less than equal

 $collection->addFieldToFilter('description', ['null' => true]);// get null values

 $collection->addFieldToFilter('created_at', ['from' => '2024-01-01', 'to' => '2024-12-31']); //date range filter



namespace Vendor\Module\Model;

use Magento\Framework\App\ResourceConnection;

class OrderData
{
    protected $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function getCompletedOrders($startDate, $endDate)
    {
        // Get DB connection
        $connection = $this->resource->getConnection();

        // Get Table Names
        $salesOrderTable = $this->resource->getTableName('sales_order');
        $customerTable = $this->resource->getTableName('customer_entity');
        $orderItemTable = $this->resource->getTableName('sales_order_item');

        // Build Query
        $query = $connection->select()
            ->from(
                ['o' => $salesOrderTable],
                ['increment_id', 'grand_total', 'status']
            )
            ->join(
                ['c' => $customerTable],
                'o.customer_id = c.entity_id',
                ['customer_id' => 'entity_id', 'customer_name' => "CONCAT(c.firstname, ' ', c.lastname)", 'customer_email' => 'email']
            )
            ->join(
                ['oi' => $orderItemTable],
                'o.entity_id = oi.order_id',
                ['total_products' => 'COUNT(oi.item_id)']
            )
            ->where('o.created_at >= ?', $startDate)
            ->where('o.created_at <= ?', $endDate)
            ->where('o.status = ?', 'complete')
            ->group('o.entity_id')
            ->order('o.created_at DESC');

        // Execute Query
        return $connection->fetchAll($query);
    }

    public function getCompletedOrders($startDate, $endDate)
    {
        // Get Database Connection
        $connection = $this->resource->getConnection();

        // Get Table Names
        $salesOrderTable = $this->resource->getTableName('sales_order');
        $customerTable = $this->resource->getTableName('customer_entity');
        $orderItemTable = $this->resource->getTableName('sales_order_item');

        // Build Query Using LEFT JOIN
        $query = $connection->select()
            ->from(
                ['o' => $salesOrderTable],
                ['increment_id', 'grand_total', 'status', 'created_at']
            )
            ->joinLeft(
                ['c' => $customerTable],  // Join Customer Table
                'o.customer_id = c.entity_id',
                ['customer_id' => 'entity_id', 'customer_name' => "CONCAT(c.firstname, ' ', c.lastname)", 'customer_email' => 'email']
            )
            ->joinLeft(
                ['oi' => $orderItemTable], // Join Order Item Table
                'o.entity_id = oi.order_id',
                ['total_products' => 'COUNT(oi.item_id)']
            )
            ->where('o.created_at >= ?', $startDate)
            ->where('o.created_at <= ?', $endDate)
            ->where('o.status = ?', 'complete')
            ->group('o.entity_id')
            ->order('o.created_at DESC');

        // Execute and Return Result
        return $connection->fetchAll($query);
    }

     public function updateOrderStatus($orderId, $newStatus)
    {
        // Get Database Connection
        $connection = $this->resource->getConnection();

        // Get Table Name
        $salesOrderTable = $this->resource->getTableName('sales_order');

        // Update Query
        $connection->update(
            $salesOrderTable,
            ['status' => $newStatus], // Set new values
            ['entity_id = ?' => $orderId] // Condition
        );

        return "Order #$orderId updated to $newStatus";
    }

}


