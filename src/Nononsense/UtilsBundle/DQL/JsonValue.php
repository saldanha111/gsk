<?php
namespace Nononsense\UtilsBundle\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * JsonValueFunction ::= "JSON_VALUE" "(" ArithmeticPrimary "," ArithmeticPrimary ")"
 */
class JsonValue extends FunctionNode
{

    public $json = null;
    public $column = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->json = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->column = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'JSON_VALUE(' .
            $this->json->dispatch($sqlWalker) . ', ' .
            $this->column->dispatch($sqlWalker) .
        ')';
    }
}