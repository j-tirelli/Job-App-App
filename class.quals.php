<?php
  class quality
  {
    private $_category_Index;
    private $_qa_Index;
    private $_quality;
    private $_answer;
    private $_achieved;
    private $_category;

    public function __construct($category_index='', $qa_Index, $quality)
    {
        $this->_category_Index = $category_index;
        $this->_qa_Index = $qa_Index;
        $this->_quality = $quality;
        if ($category_index = '') {
          $this->_category_Index = $qa_Index;
        }
        $this->_category = false;
    }

    public function make_Category()
    {

        $this->_category = true;
        return $this->_qa_Index;
    }

    public function is_Category()
    {
      return $this->_category;
    }

    public function insert_Answer($answer)
    {
        $this->_answer = $answer;
    }

    public function insert_Achieved($achieved)
    {
        $this->_achieved = $achieved;
    }

    public function quality_query_constructor()
    {
      $query_part = "('$this->_category_Index','$this->_qa_Index','$this->_quality')";
      return $query_part;
    }

    public function category_query_constructor($job_index)
    {
      if ($this->_category == true) {
        $query_part = "('$this->_qa_Index','$job_index','$this->_quality')";
        return $query_part;
      }
    }

    public function answer_query_constructor()
    {
      $query_part = "('$this->_qa_Index','$this->_answer','$this->_achieved')";
      return $query_part;
    }
  }
?>
