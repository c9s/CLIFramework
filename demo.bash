#!/bin/bash

# compgen documentation
# http://www.gnu.org/software/bash/manual/bash.html#Programmable-Completion-Builtins
# https://sixohthree.com/867/bash-completion

# To complete directory names under the /etc/
# 
#   compgen -d /etc/
#
# To complete system commands:
# 
#   compgen -c
#
# To complete bash varaible names:
# 
#   compgen -v
#
# To complete binding names:
#
#   compgen -A binding
#
# To complete built-in commands:
#
#   compgen -A builtin
#
# To complete running jobs:
#
#   compgen -A running





# This function can be used to access a tokenized list of words
# on the command line:
#
#   __demo_reassemble_comp_words_by_ref '=:'
#   if test "${words_[cword_-1]}" = -w
#   then
#       ...
#   fi
#
# The argument should be a collection of characters from the list of
# word completion separators (COMP_WORDBREAKS) to treat as ordinary
# characters.
#
# This is roughly equivalent to going back in time and setting
# COMP_WORDBREAKS to exclude those characters.  The intent is to
# make option types like --date=<type> and <rev>:<path> easy to
# recognize by treating each shell word as a single token.
#
# It is best not to set COMP_WORDBREAKS directly because the value is
# shared with other completion scripts.  By the time the completion
# function gets called, COMP_WORDS has already been populated so local
# changes to COMP_WORDBREAKS have no effect.
#
# Output: words_, cword_, cur_.

__demo_reassemble_comp_words_by_ref()
{
    local exclude i j first
    # Which word separators to exclude?
    exclude="${1//[^$COMP_WORDBREAKS]}"
    cword_=$COMP_CWORD
    if [ -z "$exclude" ]; then
        words_=("${COMP_WORDS[@]}")
        return
    fi
    # List of word completion separators has shrunk;
    # re-assemble words to complete.
    for ((i=0, j=0; i < ${#COMP_WORDS[@]}; i++, j++)); do
        # Append each nonempty word consisting of just
        # word separator characters to the current word.
        first=t
        while
            [ $i -gt 0 ] &&
            [ -n "${COMP_WORDS[$i]}" ] &&
            # word consists of excluded word separators
            [ "${COMP_WORDS[$i]//[^$exclude]}" = "${COMP_WORDS[$i]}" ]
        do
            # Attach to the previous token,
            # unless the previous token is the command name.
            if [ $j -ge 2 ] && [ -n "$first" ]; then
                ((j--))
            fi
            first=
            words_[$j]=${words_[j]}${COMP_WORDS[i]}
            if [ $i = $COMP_CWORD ]; then
                cword_=$j
            fi
            if (($i < ${#COMP_WORDS[@]} - 1)); then
                ((i++))
            else
                # Done.
                return
            fi
        done
        words_[$j]=${words_[j]}${COMP_WORDS[i]}
        if [ $i = $COMP_CWORD ]; then
            cword_=$j
        fi
    done
}

if ! type _get_comp_words_by_ref >/dev/null 2>&1; then
_get_comp_words_by_ref ()
{
    local exclude cur_ words_ cword_
    if [ "$1" = "-n" ]; then
        exclude=$2
        shift 2
    fi
    __demo_reassemble_comp_words_by_ref "$exclude"
    cur_=${words_[cword_]}
    while [ $# -gt 0 ]; do
        case "$1" in
        cur)
            cur=$cur_
            ;;
        prev)
            prev=${words_[$cword_-1]}
            ;;
        words)
            words=("${words_[@]}")
            ;;
        cword)
            cword=$cword_
            ;;
        esac
        shift
    done
}
fi


# Generates completion reply, appending a space to possible completion words,
# if necessary.
# It accepts 1 to 4 arguments:
# 1: List of possible completion words.
# 2: A prefix to be added to each possible completion word (optional).
# 3: Generate possible completion matches for this word (optional).
# 4: A suffix to be appended to each possible completion word (optional).
__mycomp ()
{
	local cur_="${3-$cur}"

	case "$cur_" in
	--*=)
		;;
	*)
		local c i=0 IFS=$' \t\n'
		for c in $1; do
			c="$c${4-}"
			if [[ $c == "$cur_"* ]]; then
				case $c in
				--*=*|*.) ;;
				*) c="$c " ;;
				esac
				COMPREPLY[i++]="${2-}$c"
			fi
		done
		;;
	esac
}

__mycompappend ()
{
	local i=${#COMPREPLY[@]}
	for x in $1; do
		if [[ "$x" == "$3"* ]]; then
			COMPREPLY[i++]="$2$x$4"
		fi
	done
}



__demo_comp_add()
{
    local c=$1
    __mycomp "complete-for-add-$c"
    # COMPREPLY=( $(compgen -d /etc/) )
}

__demo_comp_commit()
{
    local c=$1
    __mycomp "complete-for-commit-$c"
}

__demo_main ()
{
    local cur words cword prev
    _get_comp_words_by_ref -n =: cur words cword prev

    # Output application command alias mapping 
    # aliases[ alias ] = command
    declare -A alias_map
    alias_map=(["a"]="add" ["c"]="commit")

    # Define the command names
    declare -A commands
    commands=(["add"]="command to add" ["commit"]="command to commit")

    # option names defines the available options of this command
    declare -A options
    options=(["--debug"]=1 ["--verbose"]=1 ["--log-dir"]=1)

    # options_require_value: defines the required completion type for each
    # option that requires a value.
    declare -A options_require_value
    options_require_value=(["--log-dir"]="__complete_directory")

    # Get the command name chain of the current input, e.g.
    # 
    #     app asset install [arg1] [arg2] [arg3]
    #     app commit add
    #  
    # The subcommand dispatch should be done in the command complete function,
    # not in the root completion function. 
    # We should pass the argument index to the complete function.

    # c=1 start from the first argument, not the application name
    local i c=1 command
    while [ $c -lt $cword ]; do
        i="${words[c]}"

        case "$i" in
            # Ignore known options
            # XXX: handle the case of "--output directory"
            --=*) ;;
            --*) ;;
            -*) ;;
            *)
                # looks like my command, that's break the loop and dispatch to the next complete function
                if [[ -n "${commands[$i]}" ]] ; then
                    command="$i"
                    break
                elif [[ -n "${alias_map[$i]}" ]] ; then
                    command="$i"
                    break
                fi
                # If the command is not found, check if the previous argument is an option expecting a value
                break 
            ;;
        esac
        ((c++))
    done

    # If the first command is not specified, we complete the full command names
    if [[ -z "$command" ]] ; then
        case "$cur" in
            # If the current argument looks like an option, then we should complete
            --*)
                __mycomp "${!options[*]}"
                return
            ;;
            *)
                # The argument here can be an option value. e.g. --output-dir /tmp
                # The the previous one...
                if [[ -n "$prev" && -n "${options_require_value[$prev]}" ]] ; then
                    # local complete_type="${options_require_value[$prev]"}

                    # Dispatch to the meta command to get the completion:
                    #     app meta {command chain} opt {option name} valid-values
                    #
                    # For example:
                    #     php example/demo meta commit opt c valid-values
                    #
                    __mycomp "opt-val opt-val2"
                else
#                     echo -e "\nCommands:"
#                     for cmd in ${!commands[@]}; do
#                         printf "%10s -- %s\n" "$cmd" "${commands[$cmd]}"
#                     done
                    # output the command keys
                    __mycomp "${!options[*]} ${!commands[*]} ${!alias_map[*]}"
                fi
                return
            ;;
        esac
    else
        # We just found the first command, we are going to dispatch the completion handler to the next level...
        # Rewrite command alias to command name to get the correct response
        if [[ -n "${alias_map[$command]}" ]] ; then
            command="${alias_map[$command]}"
        fi
        local completion_func="__demo_comp_${command//-/_}"

        # declare the function name and call the completion function
        declare -f $completion_func >/dev/null && $completion_func $c && return
    fi
}

complete -o bashdefault -o default -o nospace -F __demo_main demo 2>/dev/null

